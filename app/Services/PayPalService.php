<?php

namespace App\Services;

use App\Models\Family;
use App\Models\Payment;
use App\Enums\PaymentStatus;
use Carbon\Carbon;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalService
{
    private function getClient(): PayPalClient
    {
        $client = new PayPalClient;
        $client->setApiCredentials(config('paypal'));
        $client->setAccessToken($client->getAccessToken());
        return $client;
    }

    public function createOrder(float $amount, int $familyId, string $returnUrl, string $cancelUrl): array
    {
        $client = $this->getClient();

        $response = $client->createOrder([
            'intent'              => 'CAPTURE',
            'purchase_units'      => [[
                'amount'      => [
                    'currency_code' => 'USD',
                    'value'         => number_format($amount, 2, '.', ''),
                ],
                'description' => 'Young Israel of Memphis — Membership Dues',
                'custom_id'   => (string)$familyId,
            ]],
            'application_context' => [
                'return_url'  => $returnUrl,
                'cancel_url'  => $cancelUrl,
                'brand_name'  => 'Young Israel of Memphis',
                'user_action' => 'PAY_NOW',
            ],
        ]);

        if (empty($response['id'])) {
            throw new \RuntimeException('PayPal createOrder failed: ' . json_encode($response));
        }

        $approveUrl = collect($response['links'])
            ->firstWhere('rel', 'approve')['href'] ?? null;

        if (!$approveUrl) {
            throw new \RuntimeException('PayPal approve URL not found in response.');
        }

        return [
            'id'          => $response['id'],
            'approve_url' => $approveUrl,
        ];
    }

    /**
     * Create a PayPal order for the JS SDK flow (no redirect URLs needed).
     * Returns the order ID to hand back to the frontend.
     */
    public function createOrderForSdk(float $amount, int $familyId, string $description): string
    {
        $client = $this->getClient();

        $response = $client->createOrder([
            'intent'         => 'CAPTURE',
            'purchase_units' => [[
                'amount'      => [
                    'currency_code' => 'USD',
                    'value'         => number_format($amount, 2, '.', ''),
                ],
                'description' => $description,
                'custom_id'   => (string)$familyId,
            ]],
            'application_context' => [
                'brand_name'          => 'Young Israel of Memphis',
                'shipping_preference' => 'NO_SHIPPING',
            ],
        ]);

        if (empty($response['id'])) {
            throw new \RuntimeException('PayPal createOrderForSdk failed: ' . json_encode($response));
        }

        return $response['id'];
    }

    public function captureOrder(string $orderId): array
    {
        $client   = $this->getClient();
        $response = $client->capturePaymentOrder($orderId);

        if (($response['status'] ?? '') !== 'COMPLETED') {
            throw new \RuntimeException('PayPal captureOrder not completed: ' . json_encode($response));
        }

        $capture = $response['purchase_units'][0]['payments']['captures'][0] ?? null;

        return [
            'transaction_id' => $capture['id'] ?? null,
            'status'         => $response['status'],
            'amount'         => (float)($capture['amount']['value'] ?? 0),
            'fee'            => (float)($capture['seller_receivable_breakdown']['paypal_fee']['value'] ?? 0),
            'raw'            => $response,
        ];
    }

    public function verifyWebhookSignature(array $headers, string $rawBody): bool
    {
        $webhookId = config('paypal.webhook_id');

        if (!$webhookId) {
            return false;
        }

        try {
            $client = $this->getClient();

            $response = $client->verifyWebHook([
                'auth_algo'         => $headers['paypal-auth-algo'][0] ?? $headers['PAYPAL-AUTH-ALGO'] ?? '',
                'cert_url'          => $headers['paypal-cert-url'][0] ?? $headers['PAYPAL-CERT-URL'] ?? '',
                'transmission_id'   => $headers['paypal-transmission-id'][0] ?? $headers['PAYPAL-TRANSMISSION-ID'] ?? '',
                'transmission_sig'  => $headers['paypal-transmission-sig'][0] ?? $headers['PAYPAL-TRANSMISSION-SIG'] ?? '',
                'transmission_time' => $headers['paypal-transmission-time'][0] ?? $headers['PAYPAL-TRANSMISSION-TIME'] ?? '',
                'webhook_id'        => $webhookId,
                'webhook_event'     => json_decode($rawBody, true),
            ]);

            return ($response['verification_status'] ?? '') === 'SUCCESS';
        } catch (\Throwable $e) {
            \Log::error('PayPal webhook verification failed: ' . $e->getMessage());
            return false;
        }
    }
}
