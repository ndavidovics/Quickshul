<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Payment;
use App\Models\PaymentToken;
use App\Models\Pledge;
use App\Services\AuditService;
use App\Services\PayPalService;
use App\Services\QuickBooksService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublicPaymentController extends Controller
{
    public function __construct(
        private PayPalService $paypal,
        private AuditService $audit
    ) {}

    public function show(string $token)
    {
        $record = PaymentToken::with('family.pledges')->where('token', $token)->first();

        if (! $record || ! $record->isValid()) {
            return view('public.pay-invalid');
        }

        $family      = $record->family;
        $openPledges = $family->pledges()->where('status', 'open')->where('balance', '>', 0)->orderByDesc('invoice_date')->get();
        $paypalClientId = config('paypal.mode') === 'sandbox'
            ? config('paypal.sandbox.client_id')
            : config('paypal.live.client_id');

        return view('public.pay', compact('family', 'openPledges', 'token', 'paypalClientId'));
    }

    public function createOrder(Request $request, string $token)
    {
        $record = PaymentToken::with('family')->where('token', $token)->first();

        if (! $record || ! $record->isValid()) {
            return response()->json(['error' => 'This payment link has expired. Please contact the office.'], 403);
        }

        $family = $record->family;

        $validated = $request->validate([
            'amounts'   => 'required|array|min:1',
            'amounts.*' => 'numeric|min:0.01|max:99999',
            'cover_fee' => 'nullable|boolean',
        ]);

        // Validate each pledge_id belongs to this family
        $pledgeIds = array_keys($validated['amounts']);
        $pledges   = Pledge::whereIn('id', $pledgeIds)
            ->where('family_id', $family->id)
            ->where('status', 'open')
            ->get()
            ->keyBy('id');

        if ($pledges->isEmpty()) {
            return response()->json(['error' => 'No valid pledges selected.'], 422);
        }

        // Build breakdown: only pledges that belong to this family
        $breakdown = [];
        $total     = 0.0;
        foreach ($validated['amounts'] as $pledgeId => $amount) {
            if (! $pledges->has($pledgeId)) continue;
            $pledge = $pledges[$pledgeId];
            $amount = min((float)$amount, (float)$pledge->balance); // cap at balance
            if ($amount <= 0) continue;
            $breakdown[$pledgeId] = $amount;
            $total += $amount;
        }

        if ($total <= 0) {
            return response()->json(['error' => 'Please enter a payment amount.'], 422);
        }

        $coverFee  = (bool)($validated['cover_fee'] ?? false);
        $feeAmount = $coverFee ? round($total * 0.02, 2) : 0.0;
        $grandTotal = round($total + $feeAmount, 2);

        $description = $pledges->count() > 1
            ? 'YIOM Pledge Payment'
            : ($pledges->first()->description ?: 'YIOM Pledge Payment');

        try {
            $orderId = $this->paypal->createOrderForSdk($grandTotal, $family->id, $description);

            // Create one pending payment; store breakdown as JSON in notes
            Payment::create([
                'family_id'       => $family->id,
                'amount'          => $total,
                'payment_date'    => today(),
                'method'          => 'paypal',
                'paypal_order_id' => $orderId,
                'description'     => $description,
                'status'          => 'pending',
                'notes'           => json_encode([
                    'pledge_breakdown' => $breakdown,
                    'cover_fee'        => $coverFee,
                    'fee_amount'       => $feeAmount,
                ]),
            ]);

            return response()->json(['id' => $orderId]);
        } catch (\Throwable $e) {
            Log::error('PublicPayment createOrder error: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to create order. Please try again.'], 500);
        }
    }

    public function captureOrder(Request $request, string $token)
    {
        $record = PaymentToken::with('family')->where('token', $token)->first();

        if (! $record || ! $record->isValid()) {
            return response()->json(['error' => 'Payment link expired.'], 403);
        }

        $family  = $record->family;
        $orderId = $request->input('orderID');

        if (! $orderId) {
            return response()->json(['error' => 'Missing order ID.'], 422);
        }

        $payment = Payment::where('paypal_order_id', $orderId)
            ->where('family_id', $family->id)
            ->where('status', 'pending')
            ->first();

        if (! $payment) {
            return response()->json(['error' => 'Order not found or already processed.'], 404);
        }

        try {
            $capture = $this->paypal->captureOrder($orderId);

            $payment->update([
                'status'                => 'completed',
                'paypal_transaction_id' => $capture['transaction_id'],
            ]);

            // Apply breakdown to pledges
            $notes      = json_decode($payment->notes, true) ?? [];
            $breakdown  = $notes['pledge_breakdown'] ?? [];
            $feeAmount  = (float)($notes['fee_amount'] ?? 0);
            $paypalFee  = $capture['fee'] ?? 0;

            // Create fee payment if user opted in
            $feePayment = null;
            if ($feeAmount > 0) {
                $feePayment = Payment::create([
                    'family_id'   => $family->id,
                    'amount'      => $feeAmount,
                    'payment_date'=> today(),
                    'method'      => 'paypal',
                    'description' => 'Cover processing fees',
                    'status'      => 'completed',
                    'notes'       => 'Processing fee opted in by member',
                ]);
            }

            foreach ($breakdown as $pledgeId => $amount) {
                $pledge = Pledge::where('id', $pledgeId)->where('family_id', $family->id)->first();
                if (! $pledge) continue;

                $newBalance = max(0, (float)$pledge->balance - (float)$amount);
                $pledge->update([
                    'balance' => $newBalance,
                    'status'  => $newBalance == 0 ? 'paid' : 'open',
                ]);
            }

            $family->recalculateBalance();

            $this->audit->log('payment.public.completed', $payment, [], [], "Public portal payment of \${$payment->amount} from {$family->name}");

            $memo = 'Portal pledge payment via public link';
            dispatch(function () use ($payment, $feePayment, $breakdown, $paypalFee, $memo) {
                $qb = app(QuickBooksService::class);

                // Build pledge→amount pairs for a single QB Payment with per-invoice line amounts.
                $pledgeAmounts = [];
                foreach ($breakdown as $pledgeId => $amount) {
                    $pledge = Pledge::find($pledgeId);
                    if ($pledge) {
                        $pledgeAmounts[] = ['pledge' => $pledge, 'amount' => (float)$amount];
                    }
                }

                try {
                    $qb->createMultiPledgePayment($payment, $pledgeAmounts, $memo);
                } catch (\Throwable $e) {
                    Log::error("QB multi-pledge payment failed (public) payment #{$payment->id}: " . $e->getMessage());
                }
                try {
                    $qb->createFeeExpense($payment, (float)$paypalFee, $memo);
                } catch (\Throwable $e) {
                    Log::error("QB fee expense failed (public) payment #{$payment->id}: " . $e->getMessage());
                }
                if ($feePayment) {
                    try {
                        $qb->createSalesReceipt($feePayment, '62');
                        Log::info("QB SalesReceipt created for processing fee payment #{$feePayment->id}");
                    } catch (\Throwable $e) {
                        Log::error("QB SalesReceipt failed for fee payment #{$feePayment->id}: " . $e->getMessage());
                    }
                }
            })->afterResponse();

            return response()->json([
                'success' => true,
                'amount'  => number_format($payment->amount, 2),
            ]);
        } catch (\Throwable $e) {
            Log::error('PublicPayment captureOrder error: ' . $e->getMessage());
            return response()->json(['error' => 'Payment capture failed. Please contact the office.'], 500);
        }
    }
}
