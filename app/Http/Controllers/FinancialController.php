<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Pledge;
use App\Services\AuditService;
use App\Services\PayPalService;
use Illuminate\Http\Request;

class FinancialController extends Controller
{
    public function __construct(
        private PayPalService $paypal,
        private AuditService $audit
    ) {}

    public function index()
    {
        $family   = auth()->user()->family;
        $payments = $family ? $family->payments()->completed()->paginate(15, ['*'], 'pp') : collect();
        $pledges  = $family ? $family->pledges()->paginate(15, ['*'], 'lp') : collect();

        return view('member.financial', compact('family', 'payments', 'pledges'));
    }

    public function exportPayments()
    {
        $family = auth()->user()->family;
        if (!$family) return back();

        $payments = $family->payments()->get();
        $filename = 'my-payments.csv';

        return response()->streamDownload(function () use ($payments) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Description', 'Amount', 'Reference', 'Status']);
            foreach ($payments as $p) {
                fputcsv($out, [
                    $p->payment_date->format('Y-m-d'),
                    $p->description ?? '',
                    number_format($p->amount, 2),
                    $p->qb_transaction_id ?? $p->qb_sales_receipt_id ?? $p->paypal_transaction_id ?? '',
                    $p->status->value,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportPledges()
    {
        $family = auth()->user()->family;
        if (!$family) return back();

        $pledges  = $family->pledges()->get();
        $filename = 'my-pledges.csv';

        return response()->streamDownload(function () use ($pledges) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Description', 'Amount', 'Balance Due', 'Status']);
            foreach ($pledges as $pledge) {
                fputcsv($out, [
                    $pledge->invoice_date->format('Y-m-d'),
                    $pledge->description ?? '',
                    number_format($pledge->amount, 2),
                    number_format($pledge->balance, 2),
                    $pledge->status,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function payNow(Request $request)
    {
        $family = auth()->user()->family;

        if (!$family) {
            return back()->withErrors(['error' => 'No family account linked.']);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1|max:99999',
        ]);

        $amount = (float)$request->amount;

        try {
            $order = $this->paypal->createOrder(
                $amount,
                $family->id,
                route('financial.pay.return'),
                route('financial.pay.cancel')
            );

            // Create pending payment record
            Payment::create([
                'family_id'       => $family->id,
                'amount'          => $amount,
                'payment_date'    => today(),
                'method'          => 'paypal',
                'paypal_order_id' => $order['id'],
                'status'          => 'pending',
                'notes'           => 'PayPal payment initiated',
            ]);

            return redirect($order['approve_url']);
        } catch (\Throwable $e) {
            \Log::error('PayNow error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Unable to initiate PayPal payment. Please try again.']);
        }
    }

    public function payReturn(Request $request)
    {
        $orderId = $request->query('token');
        $family  = auth()->user()->family;

        if (!$orderId || !$family) {
            return redirect()->route('financial')->withErrors(['error' => 'Invalid payment return.']);
        }

        $payment = Payment::where('paypal_order_id', $orderId)
            ->where('family_id', $family->id)
            ->first();

        if (!$payment) {
            return redirect()->route('financial')->withErrors(['error' => 'Payment record not found.']);
        }

        if ($payment->status->value === 'completed') {
            return redirect()->route('financial')->with('success', 'Payment already recorded.');
        }

        try {
            $capture = $this->paypal->captureOrder($orderId);

            $payment->update([
                'status'               => 'completed',
                'paypal_transaction_id'=> $capture['transaction_id'],
                'notes'                => 'PayPal payment completed',
            ]);

            $family->recalculateBalance();

            $this->audit->log('payment.paypal.completed', $payment, [], [], "PayPal payment of \${$payment->amount} completed for {$family->name}");

            // Push to QB asynchronously
            dispatch(function () use ($payment) {
                app(\App\Services\QuickBooksService::class)->createPayment($payment);
            })->afterResponse();

            return redirect()->route('financial')->with('success', 'Payment of $' . number_format($payment->amount, 2) . ' received. Thank you!');
        } catch (\Throwable $e) {
            \Log::error('PayReturn capture error: ' . $e->getMessage());
            return redirect()->route('financial')->withErrors(['error' => 'Payment capture failed. Please contact the office.']);
        }
    }

    public function payCancel(Request $request)
    {
        $orderId = $request->query('token');

        if ($orderId) {
            Payment::where('paypal_order_id', $orderId)->update(['status' => 'failed']);
        }

        return redirect()->route('financial')->with('info', 'Payment was cancelled.');
    }

    public function donateForm()
    {
        $family = auth()->user()->family;
        $mode   = config('paypal.mode', 'live');
        $paypalClientId = $mode === 'sandbox'
            ? config('paypal.sandbox.client_id')
            : config('paypal.live.client_id');
        return view('member.donate2', compact('family', 'paypalClientId'));
    }

    public function donatePay(Request $request)
    {
        $family = auth()->user()->family;

        if (!$family) {
            return back()->withErrors(['error' => 'No family account linked.']);
        }

        $request->validate([
            'amount'      => 'required|numeric|min:1|max:99999',
            'description' => 'nullable|string|max:255',
        ]);

        $amount      = (float)$request->amount;
        $description = trim($request->description ?? 'General Donation');

        try {
            $order = $this->paypal->createOrder(
                $amount,
                $family->id,
                route('donate.return'),
                route('donate.cancel')
            );

            Payment::create([
                'family_id'       => $family->id,
                'amount'          => $amount,
                'payment_date'    => today(),
                'method'          => 'paypal',
                'paypal_order_id' => $order['id'],
                'description'     => $description,
                'status'          => 'pending',
                'notes'           => 'PayPal donation initiated',
            ]);

            return redirect($order['approve_url']);
        } catch (\Throwable $e) {
            \Log::error('Donate error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Unable to initiate donation. Please try again.']);
        }
    }

    public function donateReturn(Request $request)
    {
        $orderId = $request->query('token');
        $family  = auth()->user()->family;

        if (!$orderId || !$family) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Invalid donation return.']);
        }

        $payment = Payment::where('paypal_order_id', $orderId)
            ->where('family_id', $family->id)
            ->first();

        if (!$payment) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Donation record not found.']);
        }

        if ($payment->status->value === 'completed') {
            return redirect()->route('dashboard')->with('success', 'Donation already recorded. Thank you!');
        }

        try {
            $capture = $this->paypal->captureOrder($orderId);

            $payment->update([
                'status'                => 'completed',
                'paypal_transaction_id' => $capture['transaction_id'],
                'notes'                 => 'PayPal donation completed',
            ]);

            $family->recalculateBalance();

            $this->audit->log('payment.donation.completed', $payment, [], [], "Donation of \${$payment->amount} from {$family->name}: {$payment->description}");

            $qbItemId = $this->resolveDonationItemId($payment->description ?? '');
            $paypalFee = $capture['fee'] ?? 0;
            dispatch(function () use ($payment, $qbItemId, $paypalFee) {
                $qb   = app(\App\Services\QuickBooksService::class);
                $memo = 'Portal donation via PayPal' . ($payment->description ? ' — ' . $payment->description : '');
                try {
                    $qb->createSalesReceipt($payment, $qbItemId);
                    \Log::info("QB SalesReceipt created for payment #{$payment->id} (item #{$qbItemId})");
                } catch (\Throwable $e) {
                    \Log::error("QB SalesReceipt failed for payment #{$payment->id}: " . $e->getMessage());
                }
                try {
                    $qb->createFeeExpense($payment, (float)$paypalFee, $memo);
                    \Log::info("QB fee expense created for payment #{$payment->id} (\${$paypalFee})");
                } catch (\Throwable $e) {
                    \Log::error("QB fee expense failed for payment #{$payment->id}: " . $e->getMessage());
                }
            })->afterResponse();

            return redirect()->route('dashboard')->with('success', 'Thank you for your donation of $' . number_format($payment->amount, 2) . '!');
        } catch (\Throwable $e) {
            \Log::error('DonateReturn capture error: ' . $e->getMessage());
            return redirect()->route('dashboard')->withErrors(['error' => 'Donation capture failed. Please contact the office.']);
        }
    }

    // -------------------------------------------------------------------------
    // JS SDK endpoints (AJAX / JSON)
    // -------------------------------------------------------------------------

    public function applePayCreateOrder(Request $request): \Illuminate\Http\JsonResponse
    {
        $family = auth()->user()->family;

        if (!$family) {
            return response()->json(['error' => 'No family account linked.'], 422);
        }

        $validated = $request->validate([
            'amount'      => 'required|numeric|min:1|max:99999',
            'description' => 'nullable|string|max:255',
            'pledge_id'   => 'nullable|integer|exists:pledges,id',
        ]);

        $amount      = (float)$validated['amount'];
        $description = trim($validated['description'] ?? '') ?: 'General Donation';
        $pledgeId    = $validated['pledge_id'] ?? null;

        if ($pledgeId) {
            $pledge = Pledge::where('id', $pledgeId)->where('family_id', $family->id)->first();
            if (!$pledge) {
                return response()->json(['error' => 'Invalid pledge.'], 422);
            }
        }

        try {
            $orderId = $this->paypal->createOrderForSdk($amount, $family->id, $description);

            Payment::create([
                'family_id'       => $family->id,
                'pledge_id'       => $pledgeId,
                'amount'          => $amount,
                'payment_date'    => today(),
                'method'          => 'paypal',
                'paypal_order_id' => $orderId,
                'description'     => $description,
                'status'          => 'pending',
                'notes'           => $pledgeId ? 'Apple Pay pledge payment' : 'Apple Pay donation',
            ]);

            return response()->json(['id' => $orderId]);
        } catch (\Throwable $e) {
            \Log::error('applePayCreateOrder error: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to create Apple Pay order: ' . $e->getMessage()], 500);
        }
    }

    public function donateCreateOrder(Request $request): \Illuminate\Http\JsonResponse
    {
        $family = auth()->user()->family;

        if (!$family) {
            return response()->json(['error' => 'No family account linked.'], 422);
        }

        $validated = $request->validate([
            'amount'       => 'required|numeric|min:1|max:99999',
            'description'  => 'nullable|string|max:255',
            'pledge_id'    => 'nullable|integer|exists:pledges,id',
            'donor_name'   => 'nullable|string|max:255',
            'donor_email'  => 'nullable|email',
        ]);

        $amount      = (float)$validated['amount'];
        $description = trim($validated['description'] ?? '') ?: 'General Donation';
        $pledgeId    = $validated['pledge_id'] ?? null;

        // Validate pledge belongs to this family
        if ($pledgeId) {
            $pledge = Pledge::where('id', $pledgeId)->where('family_id', $family->id)->first();
            if (!$pledge) {
                return response()->json(['error' => 'Invalid pledge.'], 422);
            }
        }

        try {
            // Gather payer information for PayPal pre-fill
            $payerInfo = array_filter([
                'name'  => $validated['donor_name'] ?? null,
                'email' => $validated['donor_email'] ?? null,
            ], fn($v) => !is_null($v));

            $orderId = $this->paypal->createOrderForSdk($amount, $family->id, $description, $payerInfo);

            Payment::create([
                'family_id'       => $family->id,
                'pledge_id'       => $pledgeId,
                'amount'          => $amount,
                'payment_date'    => today(),
                'method'          => 'paypal',
                'paypal_order_id' => $orderId,
                'description'     => $description,
                'status'          => 'pending',
                'notes'           => $pledgeId ? 'Pledge payment via JS SDK' : 'Donation via JS SDK',
            ]);

            return response()->json(['id' => $orderId]);
        } catch (\Throwable $e) {
            \Log::error('donateCreateOrder error: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to create order. Please try again.'], 500);
        }
    }

    public function donateCaptureOrder(Request $request): \Illuminate\Http\JsonResponse
    {
        $orderId = $request->input('orderID');
        $family  = auth()->user()->family;

        if (!$orderId || !$family) {
            return response()->json(['error' => 'Invalid request.'], 422);
        }

        $payment = Payment::where('paypal_order_id', $orderId)
            ->where('family_id', $family->id)
            ->where('status', 'pending')
            ->first();

        if (!$payment) {
            return response()->json(['error' => 'Order not found or already processed.'], 404);
        }

        try {
            $capture = $this->paypal->captureOrder($orderId);

            $payment->update([
                'status'                => 'completed',
                'paypal_transaction_id' => $capture['transaction_id'],
                'notes'                 => 'Donation completed via JS SDK',
            ]);

            $pledge    = $payment->pledge_id ? Pledge::find($payment->pledge_id) : null;
            $paypalFee = $capture['fee'] ?? 0;
            $memo      = 'Portal ' . ($pledge ? 'pledge payment' : 'donation') . ' via PayPal'
                       . ($payment->description ? ' — ' . $payment->description : '');

            if ($pledge) {
                $this->audit->log('payment.pledge.completed', $payment, [], [], "Pledge payment of \${$payment->amount} from {$family->name}: {$payment->description}");

                // Update pledge balance
                $newBalance = max(0, (float)$pledge->balance - (float)$payment->amount);
                $pledge->update([
                    'balance' => $newBalance,
                    'status'  => $newBalance == 0 ? 'paid' : 'open',
                ]);

                $family->recalculateBalance();

                dispatch(function () use ($payment, $pledge, $paypalFee, $memo) {
                    $qb = app(\App\Services\QuickBooksService::class);
                    try {
                        $qb->createPledgePayment($payment, $pledge, $memo);
                        \Log::info("QB Payment created for pledge #{$pledge->id}, payment #{$payment->id}");
                    } catch (\Throwable $e) {
                        \Log::error("QB pledge payment failed for payment #{$payment->id}: " . $e->getMessage());
                    }
                    try {
                        $qb->createFeeExpense($payment, (float)$paypalFee, $memo);
                        \Log::info("QB fee expense created for payment #{$payment->id} (\${$paypalFee})");
                    } catch (\Throwable $e) {
                        \Log::error("QB fee expense failed for payment #{$payment->id}: " . $e->getMessage());
                    }
                })->afterResponse();
            } else {
                $this->audit->log('payment.donation.completed', $payment, [], [], "Donation of \${$payment->amount} from {$family->name}: {$payment->description}");

                $family->recalculateBalance();

                $qbItemId = $this->resolveDonationItemId($payment->description ?? '');
                dispatch(function () use ($payment, $qbItemId, $paypalFee, $memo) {
                    $qb = app(\App\Services\QuickBooksService::class);
                    try {
                        $qb->createSalesReceipt($payment, $qbItemId);
                        \Log::info("QB SalesReceipt created for payment #{$payment->id} (item #{$qbItemId})");
                    } catch (\Throwable $e) {
                        \Log::error("QB SalesReceipt failed for payment #{$payment->id}: " . $e->getMessage());
                    }
                    try {
                        $qb->createFeeExpense($payment, (float)$paypalFee, $memo);
                        \Log::info("QB fee expense created for payment #{$payment->id} (\${$paypalFee})");
                    } catch (\Throwable $e) {
                        \Log::error("QB fee expense failed for payment #{$payment->id}: " . $e->getMessage());
                    }
                })->afterResponse();
            }

            return response()->json([
                'success'     => true,
                'amount'      => number_format($payment->amount, 2),
                'description' => $payment->description,
            ]);
        } catch (\Throwable $e) {
            \Log::error('donateCaptureOrder error: ' . $e->getMessage());
            return response()->json(['error' => 'Payment capture failed. Please contact the office.'], 500);
        }
    }

    /**
     * Apple Pay — server-side confirm-payment-source + capture in one call.
     * The JS sends us the raw Apple Pay token; we hit PayPal's REST API directly
     * so we don't rely on the client-side applepay.confirmOrder() SDK method.
     */
    public function applePayCapture(Request $request): \Illuminate\Http\JsonResponse
    {
        $orderId       = $request->input('orderID');
        $applePayToken = $request->input('applePayToken');
        $billingContact = $request->input('billingContact', []);
        $family        = auth()->user()->family;

        if (!$orderId || !$applePayToken || !$family) {
            return response()->json(['error' => 'Invalid request.'], 422);
        }

        $payment = Payment::where('paypal_order_id', $orderId)
            ->where('family_id', $family->id)
            ->where('status', 'pending')
            ->first();

        if (!$payment) {
            return response()->json(['error' => 'Order not found or already processed.'], 404);
        }

        try {
            // Step 1: confirm payment source with Apple Pay token (server-side)
            $this->paypal->confirmApplePayOrder($orderId, $applePayToken, $billingContact);

            // Step 2: capture
            $capture = $this->paypal->captureOrder($orderId);

            $payment->update([
                'status'                => 'completed',
                'paypal_transaction_id' => $capture['transaction_id'],
                'notes'                 => 'Apple Pay donation completed',
            ]);

            $pledge    = $payment->pledge_id ? Pledge::find($payment->pledge_id) : null;
            $paypalFee = $capture['fee'] ?? 0;
            $memo      = 'Portal ' . ($pledge ? 'pledge payment' : 'donation') . ' via Apple Pay'
                       . ($payment->description ? ' — ' . $payment->description : '');

            if ($pledge) {
                $this->audit->log('payment.pledge.completed', $payment, [], [], "Pledge payment of \${$payment->amount} from {$family->name}: {$payment->description}");

                $newBalance = max(0, (float)$pledge->balance - (float)$payment->amount);
                $pledge->update([
                    'balance' => $newBalance,
                    'status'  => $newBalance == 0 ? 'paid' : 'open',
                ]);

                $family->recalculateBalance();

                dispatch(function () use ($payment, $pledge, $paypalFee, $memo) {
                    $qb = app(\App\Services\QuickBooksService::class);
                    try {
                        $qb->createPledgePayment($payment, $pledge, $memo);
                        \Log::info("QB Payment created for pledge #{$pledge->id}, payment #{$payment->id}");
                    } catch (\Throwable $e) {
                        \Log::error("QB pledge payment failed for payment #{$payment->id}: " . $e->getMessage());
                    }
                    try {
                        $qb->createFeeExpense($payment, (float)$paypalFee, $memo);
                    } catch (\Throwable $e) {
                        \Log::error("QB fee expense failed for payment #{$payment->id}: " . $e->getMessage());
                    }
                })->afterResponse();
            } else {
                $this->audit->log('payment.donation.completed', $payment, [], [], "Apple Pay donation of \${$payment->amount} from {$family->name}: {$payment->description}");

                $family->recalculateBalance();

                $qbItemId = $this->resolveDonationItemId($payment->description ?? '');
                dispatch(function () use ($payment, $qbItemId, $paypalFee, $memo) {
                    $qb = app(\App\Services\QuickBooksService::class);
                    try {
                        $qb->createSalesReceipt($payment, $qbItemId);
                        \Log::info("QB SalesReceipt created for Apple Pay payment #{$payment->id}");
                    } catch (\Throwable $e) {
                        \Log::error("QB SalesReceipt failed for payment #{$payment->id}: " . $e->getMessage());
                    }
                    try {
                        $qb->createFeeExpense($payment, (float)$paypalFee, $memo);
                    } catch (\Throwable $e) {
                        \Log::error("QB fee expense failed for payment #{$payment->id}: " . $e->getMessage());
                    }
                })->afterResponse();
            }

            return response()->json([
                'success'     => true,
                'amount'      => number_format($payment->amount, 2),
                'description' => $payment->description,
            ]);
        } catch (\Throwable $e) {
            \Log::error('applePayCapture error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // -------------------------------------------------------------------------

    /**
     * Resolve the QuickBooks Item ID to use for a donation SalesReceipt
     * based on keywords in the description.
     *
     * QB item IDs (from qbo.intuit.com):
     *   25  = Aliyahs
     *   60  = Rabbi Discretionary Fund
     *   62  = General Donations  (default)
     */
    private function resolveDonationItemId(string $description): string
    {
        $desc = strtolower($description);

        if (str_contains($desc, 'aliyah') || str_contains($desc, 'aliya')) {
            return '25'; // Aliyahs
        }

        if (str_contains($desc, 'discretionary') || str_contains($desc, 'rdf')) {
            return '60'; // Rabbi Discretionary Fund
        }

        return '62'; // General Donations
    }

    public function donateCancel(Request $request)
    {
        $orderId = $request->query('token');

        if ($orderId) {
            Payment::where('paypal_order_id', $orderId)->update(['status' => 'failed']);
        }

        return redirect()->route('dashboard')->with('info', 'Donation was cancelled.');
    }
}
