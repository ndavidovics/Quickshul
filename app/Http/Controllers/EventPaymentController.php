<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Event;
use App\Models\EventPayment;
use App\Models\Payment;
use App\Services\PayPalService;
use App\Services\QuickBooksService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventPaymentController extends Controller
{
    public function __construct(
        private PayPalService $paypal,
        private QuickBooksService $qb,
    ) {}

    public function show(string $tenantSlug, string $eventSlug)
    {
        $event = Event::with('ticketTypes')
            ->where('slug', $eventSlug)
            ->firstOrFail();

        if ($event->status === 'closed') {
            return view('public.event-closed', compact('event'));
        }

        $paypalClientId = config('paypal.mode') === 'sandbox'
            ? config('paypal.sandbox.client_id')
            : config('paypal.live.client_id');

        // Pre-fill if logged in
        $prefill = null;
        if (auth()->check()) {
            $user = auth()->user();
            $prefill = [
                'name'  => $user->name,
                'email' => $user->email,
            ];
        }

        return view('public.event', compact('event', 'paypalClientId', 'prefill'));
    }

    public function createOrder(Request $request, string $tenantSlug, string $eventSlug)
    {
        $event = Event::with('ticketTypes')->where('slug', $eventSlug)->firstOrFail();

        if ($event->status === 'closed') {
            return response()->json(['error' => 'This event is closed.'], 422);
        }

        $validated = $request->validate([
            'payer_name'  => 'required|string|max:200',
            'payer_email' => 'required|email',
            'quantities'  => 'required|array',
            'quantities.*' => 'integer|min:0',
        ]);

        // Build ticket breakdown and calculate total
        $ticketMap = $event->ticketTypes->keyBy('id');
        $subtotal  = 0;
        $quantities = [];

        foreach ($validated['quantities'] as $typeId => $qty) {
            $qty = (int)$qty;
            if ($qty <= 0) continue;
            $type = $ticketMap->get($typeId);
            if (!$type) continue;
            $subtotal += $type->price * $qty;
            $quantities[$typeId] = $qty;
        }

        if (empty($quantities) || $subtotal <= 0) {
            return response()->json(['error' => 'Please select at least one ticket.'], 422);
        }

        // Apply family max cap
        $total = $subtotal;
        if ($event->family_max && $total > $event->family_max) {
            $total = $event->family_max;
        }

        $tenant = app('tenant');

        // Create pending event_payment record
        $ep = EventPayment::create([
            'tenant_id'       => $tenant->id,
            'event_id'        => $event->id,
            'family_id'       => auth()->check() ? auth()->user()->family_id : null,
            'payer_name'      => $validated['payer_name'],
            'payer_email'     => $validated['payer_email'],
            'ticket_quantities' => $quantities,
            'subtotal'        => $subtotal,
            'total_amount'    => $total,
            'status'          => 'pending',
        ]);

        try {
            $description = $tenant->name . ' — ' . $event->name;
            $orderId = $this->paypal->createOrderForSdk(
                (float)$total,
                $ep->id,  // use event_payment id as custom_id
                $description,
                ['name' => $validated['payer_name'], 'email' => $validated['payer_email']]
            );

            $ep->update(['paypal_order_id' => $orderId]);

            return response()->json(['id' => $orderId]);
        } catch (\Throwable $e) {
            Log::error('Event PayPal createOrder failed', ['error' => $e->getMessage(), 'ep_id' => $ep->id]);
            $ep->update(['status' => 'failed']);
            return response()->json(['error' => 'PayPal order creation failed. Please try again.'], 500);
        }
    }

    public function captureOrder(Request $request, string $tenantSlug, string $eventSlug)
    {
        $request->validate(['order_id' => 'required|string']);
        $orderId = $request->input('order_id');

        $ep = EventPayment::where('paypal_order_id', $orderId)
            ->where('status', 'pending')
            ->firstOrFail();

        $event = $ep->event;

        try {
            $result = $this->paypal->captureOrder($orderId);
        } catch (\Throwable $e) {
            Log::error('Event PayPal capture failed', ['error' => $e->getMessage(), 'ep_id' => $ep->id]);
            $ep->update(['status' => 'failed']);
            return response()->json(['error' => 'Payment capture failed. Please contact us.'], 500);
        }

        $ep->update([
            'paypal_transaction_id' => $result['transaction_id'],
            'status'                => 'completed',
        ]);

        // QB sync only for logged-in members with a family mapping
        if ($ep->family_id && $event->qb_item_id && $this->qb->isConnected()) {
            try {
                DB::transaction(function () use ($ep, $event) {
                    // Create a Payment record in the main payments table for QB
                    $payment = Payment::create([
                        'tenant_id'    => $ep->tenant_id,
                        'family_id'    => $ep->family_id,
                        'amount'       => $ep->total_amount,
                        'payment_date' => now()->toDateString(),
                        'method'       => 'paypal',
                        'description'  => $event->name . ' — Event Payment',
                        'status'       => PaymentStatus::Completed,
                    ]);

                    $receiptId = $this->qb->createSalesReceipt($payment, $event->qb_item_id);
                    $ep->update(['qb_sales_receipt_id' => $receiptId]);
                });
            } catch (\Throwable $e) {
                Log::error('Event QB sync failed', ['error' => $e->getMessage(), 'ep_id' => $ep->id]);
                // Non-fatal — payment captured successfully, QB sync can be retried
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment successful! Thank you for your purchase.',
        ]);
    }
}
