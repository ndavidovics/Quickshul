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
                'description' => ($orgName = app()->bound('tenant') ? app('tenant')->name : config('app.name')) . ' — Membership Dues',
                'custom_id'   => (string)$familyId,
            ]],
            'application_context' => [
                'return_url'  => $returnUrl,
                'cancel_url'  => $cancelUrl,
                'brand_name'  => app()->bound('tenant') ? app('tenant')->name : config('app.name'),
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
     * 
     * @param float $amount
     * @param int $familyId
     * @param string $description
     * @param array $payer Optional payer information: ['name', 'email', 'phone']
     * @return string The order ID
     */
    public function createOrderForSdk(float $amount, int $familyId, string $description, array $payer = []): string
    {
        $client = $this->getClient();

        $order = [
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
                'brand_name'          => app()->bound('tenant') ? app('tenant')->name : config('app.name'),
                'shipping_preference' => 'NO_SHIPPING',
            ],
        ];

        // Add payer information if provided (for pre-filling hosted payment form)
        if (!empty($payer)) {
            $payerData = [];
            
            // Add payer name (PayPal uses this to pre-fill the payment form)
            if (!empty($payer['name'])) {
                $nameParts = explode(' ', trim($payer['name']), 2);
                $payerData['name'] = [
                    'given_name' => $nameParts[0] ?? '',
                    'surname'    => $nameParts[1] ?? '',
                ];
            }
            
            // Add email (PayPal pre-fills this in the hosted form)
            if (!empty($payer['email'])) {
                $payerData['email_address'] = $payer['email'];
            }
            
            if (!empty($payerData)) {
                $order['payer'] = $payerData;
            }
        }

        $response = $client->createOrder($order);

        if (empty($response['id'])) {
            throw new \RuntimeException('PayPal createOrderForSdk failed: ' . json_encode($response));
        }

        return $response['id'];
    }

    /**
     * Create a PayPal order pre-tagged for Apple Pay payment source.
     * Specifying payment_source.apple_pay at creation time tells PayPal's
     * backend to prepare certificate/decryption context for this order.
     */
    public function createOrderForApplePay(float $amount, int $familyId, string $description): string
    {
        $client    = new \Srmklive\PayPal\Services\PayPal;
        $client->setApiCredentials(config('paypal'));
        $tokenData   = $client->getAccessToken();
        $accessToken = $tokenData['access_token'] ?? null;

        if (!$accessToken) {
            throw new \RuntimeException('PayPal: unable to obtain access token.');
        }

        $mode    = config('paypal.mode', 'live');
        $baseUrl = $mode === 'sandbox'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';

        $body = [
            'intent'         => 'CAPTURE',
            'payment_source' => [
                'apple_pay' => (object)[], // {} — signals this order is for Apple Pay
            ],
            'purchase_units' => [[
                'amount'      => [
                    'currency_code' => 'USD',
                    'value'         => number_format($amount, 2, '.', ''),
                ],
                'description' => $description,
                'custom_id'   => (string)$familyId,
            ]],
        ];

        $response = \Illuminate\Support\Facades\Http::withToken($accessToken)
            ->asJson()
            ->post("{$baseUrl}/v2/checkout/orders", $body);

        if (!$response->successful() || empty($response->json('id'))) {
            throw new \RuntimeException('PayPal Apple Pay order creation failed: ' . $response->body());
        }

        return $response->json('id');
    }

    /**
     * Confirm an order's payment source as Apple Pay (server-side).
     * Calls POST /v2/checkout/orders/{id}/confirm-payment-source with the
     * encrypted Apple Pay token, then returns the confirmed order data.
     */
    public function confirmApplePayOrder(string $orderId, array $applePayToken, array $billingContact = []): array
    {
        $client    = new \Srmklive\PayPal\Services\PayPal;
        $client->setApiCredentials(config('paypal'));
        $tokenData   = $client->getAccessToken();
        $accessToken = $tokenData['access_token'] ?? null;

        if (!$accessToken) {
            throw new \RuntimeException('PayPal: unable to obtain access token for Apple Pay confirm.');
        }

        $mode    = config('paypal.mode', 'live');
        $baseUrl = $mode === 'sandbox'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';

        $body = [
            'payment_source' => [
                'apple_pay' => [
                    'token' => $applePayToken,
                ],
            ],
        ];

        $response = \Illuminate\Support\Facades\Http::withToken($accessToken)
            ->asJson()
            ->post("{$baseUrl}/v2/checkout/orders/{$orderId}/confirm-payment-source", $body);

        if (!$response->successful()) {
            throw new \RuntimeException('PayPal Apple Pay confirm-payment-source failed: ' . $response->body());
        }

        return $response->json();
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
