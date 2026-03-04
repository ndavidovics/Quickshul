<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Payment;
use App\Services\AuditService;
use App\Services\PayPalService;
use App\Services\QuickBooksService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PayPalWebhookController extends Controller
{
    public function __construct(
        private PayPalService $paypal,
        private QuickBooksService $qbService,
        private AuditService $audit
    ) {}

    public function handle(Request $request): Response
    {
        $rawBody = $request->getContent();

        if (!$this->paypal->verifyWebhookSignature($request->headers->all(), $rawBody)) {
            Log::warning('PayPal webhook: invalid signature from ' . $request->ip());
            return response('', 400);
        }

        $payload   = json_decode($rawBody, true);
        $eventType = $payload['event_type'] ?? '';

        match($eventType) {
            'PAYMENT.CAPTURE.COMPLETED' => $this->handleCaptureCompleted($payload),
            default                     => Log::info("PayPal webhook: unhandled event {$eventType}"),
        };

        return response('', 200);
    }

    private function handleCaptureCompleted(array $payload): void
    {
        $orderId       = $payload['resource']['supplementary_data']['related_ids']['order_id'] ?? null;
        $transactionId = $payload['resource']['id'] ?? null;
        $amount        = (float)($payload['resource']['amount']['value'] ?? 0);

        if (!$orderId) {
            Log::error('PayPal webhook: no order_id in CAPTURE.COMPLETED payload');
            return;
        }

        $payment = Payment::where('paypal_order_id', $orderId)->first();

        if (!$payment) {
            Log::warning("PayPal webhook: no pending payment found for order {$orderId}");
            return;
        }

        if ($payment->status->value === 'completed') {
            Log::info("PayPal webhook: payment {$payment->id} already completed (idempotent)");
            return;
        }

        $payment->update([
            'status'                => 'completed',
            'paypal_transaction_id' => $transactionId,
            'notes'                 => ($payment->notes ?? '') . ' [Webhook confirmed]',
        ]);

        $family             = $payment->family;
        $family->total_paid = Payment::where('family_id', $family->id)->completed()->sum('amount');
        $family->recalculateBalance();

        $this->audit->log('payment.webhook.completed', $payment, [], [], "PayPal webhook confirmed \${$payment->amount} for {$family->name}");

        // Push to QB
        try {
            if ($this->qbService->isConnected()) {
                $qbId = $this->qbService->createPayment($payment);
                if ($qbId) {
                    $payment->update(['qb_transaction_id' => $qbId]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('PayPal webhook QB push failed: ' . $e->getMessage());
        }
    }
}
