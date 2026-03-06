<?php

namespace App\Services;

use App\Enums\MembershipType;
use App\Exceptions\QuickBooksNotConnectedException;
use App\Models\Family;
use App\Models\Payment;
use App\Models\Pledge;
use App\Models\QbConnection;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;

class QuickBooksService
{
    public function getConnection(): ?QbConnection
    {
        return QbConnection::first();
    }

    public function isConnected(): bool
    {
        $conn = $this->getConnection();
        return $conn !== null && !$conn->isRefreshTokenExpired();
    }

    public function refreshTokenIfNeeded(): bool
    {
        $conn = $this->getConnection();
        if (!$conn) return false;
        if (!$conn->isAccessTokenExpired()) return true;

        try {
            $dataService = DataService::Configure([
                'auth_mode'     => 'oauth2',
                'ClientID'      => config('services.quickbooks.client_id'),
                'ClientSecret'  => config('services.quickbooks.client_secret'),
                'accessTokenKey'=> $conn->access_token,
                'refreshTokenKey'=> $conn->refresh_token,
                'QBORealmID'    => $conn->realm_id,
                'baseUrl'       => config('services.quickbooks.environment') === 'sandbox' ? 'Development' : 'Production',
            ]);

            $oauth2Helper   = $dataService->getOAuth2LoginHelper();
            $accessToken    = $oauth2Helper->refreshAccessTokenWithRefreshToken($conn->refresh_token);

            $conn->update([
                'access_token'            => $accessToken->getAccessToken(),
                'refresh_token'           => $accessToken->getRefreshToken(),
                'access_token_expires_at' => now()->addSeconds($accessToken->getAccessTokenValidationPeriodInSeconds()),
                'refresh_token_expires_at'=> now()->addDays(100),
            ]);

            return true;
        } catch (\Throwable $e) {
            \Log::error('QB token refresh failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getClient(): DataService
    {
        if (!$this->isConnected()) {
            throw new QuickBooksNotConnectedException();
        }

        if (!$this->refreshTokenIfNeeded()) {
            throw new \RuntimeException('QuickBooks access token is expired and could not be refreshed. Please reconnect QuickBooks.');
        }

        $conn = $this->getConnection();

        return DataService::Configure([
            'auth_mode'      => 'oauth2',
            'ClientID'       => config('services.quickbooks.client_id'),
            'ClientSecret'   => config('services.quickbooks.client_secret'),
            'accessTokenKey' => $conn->access_token,
            'refreshTokenKey'=> $conn->refresh_token,
            'QBORealmID'     => $conn->realm_id,
            'baseUrl'        => config('services.quickbooks.environment') === 'sandbox' ? 'Development' : 'Production',
        ]);
    }

    public function getAuthorizationUrl(): string
    {
        $oauth2Helper = new OAuth2LoginHelper(
            config('services.quickbooks.client_id'),
            config('services.quickbooks.client_secret'),
            config('services.quickbooks.redirect_uri'),
            'com.intuit.quickbooks.accounting'
        );

        return $oauth2Helper->getAuthorizationCodeURL();
    }

    public function exchangeCodeForTokens(string $code, string $realmId): QbConnection
    {
        $oauth2Helper = new OAuth2LoginHelper(
            config('services.quickbooks.client_id'),
            config('services.quickbooks.client_secret'),
            config('services.quickbooks.redirect_uri'),
            'com.intuit.quickbooks.accounting'
        );

        $accessToken = $oauth2Helper->exchangeAuthorizationCodeForToken($code, $realmId);

        return QbConnection::updateOrCreate(
            ['realm_id' => $realmId],
            [
                'access_token'             => $accessToken->getAccessToken(),
                'refresh_token'            => $accessToken->getRefreshToken(),
                'access_token_expires_at'  => now()->addSeconds($accessToken->getAccessTokenValidationPeriodInSeconds()),
                'refresh_token_expires_at' => now()->addDays(100),
                'connected_by_user_id'     => auth()->id(),
            ]
        );
    }

    /**
     * Returns a map of QB Item ID → name string.
     */
    public function getItems(): array
    {
        $client   = $this->getClient();
        $map      = [];
        $startPos = 1;

        do {
            $items = $client->Query("SELECT * FROM Item STARTPOSITION {$startPos} MAXRESULTS 1000");
            $error = $client->getLastError();
            if ($error || !$items) break;

            $batch = (array)$items;
            foreach ($batch as $item) {
                if (!empty($item->Id)) {
                    $map[(string)$item->Id] = $item->Name ?? '';
                }
            }
            $startPos += 1000;
        } while (count($batch) === 1000);

        return $map;
    }

    /**
     * Build a human-readable description string from invoice/receipt line items.
     */
    public function buildLineDescription(object $txn, array $itemMap): string
    {
        $raw = $txn->Line ?? null;
        if (!$raw) return '';

        // Single line item comes as an object, multiple as an array — never use (array) cast
        $lines = is_array($raw) ? $raw : [$raw];

        $parts = [];
        foreach ($lines as $line) {
            if (empty($line->DetailType) || $line->DetailType !== 'SalesItemLineDetail') continue;

            // Use line Description if set, otherwise look up the item name
            $desc   = trim($line->Description ?? '');
            $itemId = (string)($line->SalesItemLineDetail->ItemRef ?? '');
            $name   = $itemId ? ($itemMap[$itemId] ?? '') : '';

            $label = $desc ?: $name;
            if ($label && !in_array($label, $parts)) {
                $parts[] = $label;
            }
        }

        return implode(', ', $parts);
    }

    /**
     * Fetch all Invoices from QB, keyed by invoice ID.
     * Returns array of ['family_id_ref' => string, 'data' => [...pledge fields...]]
     */
    public function getInvoices(array $itemMap, ?\DateTimeInterface $changedSince = null): array
    {
        $client   = $this->getClient();
        $all      = [];
        $startPos = 1;

        do {
            $where = $changedSince
                ? "WHERE Metadata.LastUpdatedTime > '" . \Carbon\Carbon::instance($changedSince)->utc()->format('Y-m-d\TH:i:s') . "'"
                : '';

            $sql      = "SELECT * FROM Invoice {$where} STARTPOSITION {$startPos} MAXRESULTS 1000";
            $invoices = $client->Query($sql);
            $error    = $client->getLastError();
            if ($error || !$invoices) break;

            $batch = (array)$invoices;
            foreach ($batch as $inv) {
                $custId = (string)($inv->CustomerRef ?? '');
                if (!$custId) continue;

                $all[(string)$inv->Id] = [
                    'qb_customer_id' => $custId,
                    'qb_invoice_id'  => (string)$inv->Id,
                    'description'    => $this->buildLineDescription($inv, $itemMap),
                    'amount'         => (float)($inv->TotalAmt ?? 0),
                    'balance'        => (float)($inv->Balance ?? 0),
                    'invoice_date'   => $inv->TxnDate ?? now()->format('Y-m-d'),
                    'due_date'       => $inv->DueDate ?? null,
                    'status'         => ((float)($inv->Balance ?? 0) === 0.0) ? 'paid' : 'open',
                ];
            }

            $startPos += 1000;
        } while (count($batch) === 1000);

        return $all;
    }

    /**
     * Fetch specific invoices by their QB IDs. Used to refresh pledge balances
     * after a payment sync when the invoice delta query misses them (QB does not
     * update an invoice's LastUpdatedTime when a payment is applied to it).
     * Returns array keyed by QB invoice ID → invoice object.
     */
    public function getInvoicesByIds(array $ids): array
    {
        if (empty($ids)) return [];

        $client = $this->getClient();
        $all    = [];

        // Chunk to avoid QB query string limits
        foreach (array_chunk($ids, 100) as $chunk) {
            $idList   = implode("','", $chunk);
            $sql      = "SELECT * FROM Invoice WHERE Id IN ('{$idList}')";
            $invoices = $client->Query($sql);
            $error    = $client->getLastError();
            if ($error || !$invoices) continue;

            foreach ((array)$invoices as $inv) {
                $all[(string)$inv->Id] = $inv;
            }
        }

        return $all;
    }

    /**
     * Fetch all SalesReceipts from QB.
     */
    public function getSalesReceipts(array $itemMap, ?\DateTimeInterface $changedSince = null): array
    {
        $client   = $this->getClient();
        $all      = [];
        $startPos = 1;

        do {
            $where = $changedSince
                ? "WHERE Metadata.LastUpdatedTime > '" . \Carbon\Carbon::instance($changedSince)->utc()->format('Y-m-d\TH:i:s') . "'"
                : '';

            $sql      = "SELECT * FROM SalesReceipt {$where} STARTPOSITION {$startPos} MAXRESULTS 1000";
            $receipts = $client->Query($sql);
            $error    = $client->getLastError();
            if ($error || !$receipts) break;

            $batch = (array)$receipts;
            foreach ($batch as $sr) {
                $custId = (string)($sr->CustomerRef ?? '');
                if (!$custId) continue;

                $all[] = [
                    'qb_customer_id'      => $custId,
                    'qb_sales_receipt_id' => (string)$sr->Id,
                    'amount'              => (float)($sr->TotalAmt ?? 0),
                    'payment_date'        => $sr->TxnDate ?? now()->format('Y-m-d'),
                    'description'         => $this->buildLineDescription($sr, $itemMap),
                    'method'              => 'quickbooks',
                    'status'              => 'completed',
                ];
            }

            $startPos += 1000;
        } while (count($batch) === 1000);

        return $all;
    }

    /**
     * Returns a map of QB CustomerType ID → name string.
     */
    public function getCustomerTypes(): array
    {
        $client = $this->getClient();
        $types  = $client->Query('SELECT * FROM CustomerType MAXRESULTS 200');
        $error  = $client->getLastError();

        if ($error || !$types) {
            return [];
        }

        $map = [];
        foreach ((array)$types as $t) {
            if (!empty($t->Id)) {
                $map[(string)$t->Id] = $t->Name ?? '';
            }
        }

        return $map;
    }

    public function getCustomers(?\DateTimeInterface $changedSince = null): array
    {
        $client   = $this->getClient();
        $all      = [];
        $startPos = 1;

        do {
            if ($changedSince) {
                $dt  = \Carbon\Carbon::instance($changedSince)->utc()->format('Y-m-d\TH:i:s');
                $sql = "SELECT * FROM Customer WHERE Metadata.LastUpdatedTime > '{$dt}' STARTPOSITION {$startPos} MAXRESULTS 1000";
            } else {
                $sql = "SELECT * FROM Customer STARTPOSITION {$startPos} MAXRESULTS 1000";
            }

            $customers = $client->Query($sql);
            $error     = $client->getLastError();

            if ($error) {
                throw new \RuntimeException('QB getCustomers error: ' . $error->getResponseBody());
            }

            $batch = is_array($customers) ? $customers : ($customers ? [$customers] : []);
            $all   = array_merge($all, $batch);
            $startPos += 1000;
        } while (count($batch) === 1000);

        return $all;
    }

    /**
     * Fetch all Invoices from QB (pledges).
     * Returns array keyed by QB customer ID → total pledged amount.
     */
    public function getInvoiceTotalsByCustomer(): array
    {
        $client = $this->getClient();
        $totals = [];
        $startPos = 1;

        do {
            $sql      = "SELECT * FROM Invoice STARTPOSITION {$startPos} MAXRESULTS 1000";
            $invoices = $client->Query($sql);
            $error    = $client->getLastError();

            if ($error || !$invoices) break;

            $batch = (array)$invoices;
            foreach ($batch as $inv) {
                $custId = (string)($inv->CustomerRef ?? '');
                if (!$custId) continue;
                $totals[$custId] = ($totals[$custId] ?? 0) + (float)($inv->TotalAmt ?? 0);
            }

            $startPos += 1000;
        } while (count($batch) === 1000);

        return $totals;
    }

    public function getTransactions(?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null): array
    {
        $client = $this->getClient();

        $start = $startDate
            ? \Carbon\Carbon::instance($startDate)->format('Y-m-d')
            : '2000-01-01';

        $end = $endDate
            ? \Carbon\Carbon::instance($endDate)->format('Y-m-d')
            : now()->format('Y-m-d');

        $all      = [];
        $startPos = 1;

        do {
            $sql      = "SELECT * FROM Payment WHERE TxnDate >= '{$start}' AND TxnDate <= '{$end}' STARTPOSITION {$startPos} MAXRESULTS 1000";
            $payments = $client->Query($sql);
            $error    = $client->getLastError();

            if ($error) {
                throw new \RuntimeException('QB getTransactions error: ' . $error->getResponseBody());
            }

            $batch = is_array($payments) ? $payments : ($payments ? [$payments] : []);
            $all   = array_merge($all, $batch);
            $startPos += 1000;
        } while (count($batch) === 1000);

        return $all;
    }

    public function createSalesReceipt(Payment $payment, string $itemId): ?string
    {
        $client = $this->getClient();
        $family = $payment->family;

        if (!$family->qb_customer_id) return null;

        $memo = 'Portal donation via PayPal'
              . ($payment->description ? ' — ' . $payment->description : '');

        $receipt = \QuickBooksOnline\API\Facades\SalesReceipt::create([
            'CustomerRef'         => ['value' => $family->qb_customer_id],
            'TxnDate'             => $payment->payment_date->format('Y-m-d'),
            'PrivateNote'         => $memo,
            'PaymentMethodRef'    => ['value' => '9'],   // PayPal
            'DepositToAccountRef' => ['value' => '975'], // Paypal for Payments
            'Line'                => [[
                'Amount'      => (float)$payment->amount,
                'DetailType'  => 'SalesItemLineDetail',
                'Description' => $payment->description ?: 'Donation',
                'SalesItemLineDetail' => [
                    'ItemRef'   => ['value' => $itemId],
                    'Qty'       => 1,
                    'UnitPrice' => (float)$payment->amount,
                ],
            ]],
        ]);

        $result = $client->Add($receipt);
        $error  = $client->getLastError();

        if ($error) {
            throw new \RuntimeException('QB createSalesReceipt error: ' . $error->getResponseBody());
        }

        $receiptId = $result->Id ?? null;

        if ($receiptId) {
            $payment->update(['qb_sales_receipt_id' => $receiptId]);
        }

        return $receiptId;
    }

    /**
     * Create a QB Payment linked to the pledge's QB Invoice.
     * Uses PayPal as payment method, deposited to Paypal for Payments.
     */
    public function createPledgePayment(Payment $payment, Pledge $pledge, string $memo): ?string
    {
        $client = $this->getClient();
        $family = $payment->family;

        if (!$family->qb_customer_id) return null;

        $lines = [];

        // Link to the QB invoice if we have one; otherwise it's an unapplied payment
        if ($pledge->qb_invoice_id) {
            $lines[] = [
                'Amount'    => (float)$payment->amount,
                'LinkedTxn' => [[
                    'TxnId'   => $pledge->qb_invoice_id,
                    'TxnType' => 'Invoice',
                ]],
            ];
        }

        $qbPayment = \QuickBooksOnline\API\Facades\Payment::create([
            'CustomerRef'         => ['value' => $family->qb_customer_id],
            'TotalAmt'            => (float)$payment->amount,
            'TxnDate'             => $payment->payment_date->format('Y-m-d'),
            'PrivateNote'         => $memo,
            'PaymentMethodRef'    => ['value' => '9'],   // PayPal
            'DepositToAccountRef' => ['value' => '975'], // Paypal for Payments
            'Line'                => $lines,
        ]);

        $result = $client->Add($qbPayment);
        $error  = $client->getLastError();

        if ($error) {
            throw new \RuntimeException('QB createPledgePayment error: ' . $error->getResponseBody());
        }

        $qbPaymentId = $result->Id ?? null;

        if ($qbPaymentId) {
            $payment->update(['qb_transaction_id' => $qbPaymentId]);
        }

        return $qbPaymentId;
    }

    /**
     * Create a QB expense (Purchase) for the PayPal transaction fee.
     *
     * QB IDs:
     *   Vendor  1053 = PayPal
     *   Account  462 = [5920] Credit Card Fees  (expense line)
     *   Account  975 = Paypal for Payments      (payment account)
     */
    public function createFeeExpense(Payment $payment, float $fee, string $memo): ?string
    {
        if ($fee <= 0) return null;

        $client = $this->getClient();

        $expense = \QuickBooksOnline\API\Facades\Purchase::create([
            'PaymentType'    => 'Cash',                         // paid from bank-type account
            'AccountRef'     => ['value' => '975'],             // Paypal for Payments
            'EntityRef'      => ['value' => '1053', 'type' => 'Vendor'], // PayPal vendor
            'TxnDate'        => $payment->payment_date->format('Y-m-d'),
            'PrivateNote'    => $memo,
            'Line'           => [[
                'Amount'     => $fee,
                'DetailType' => 'AccountBasedExpenseLineDetail',
                'Description'=> $memo,
                'AccountBasedExpenseLineDetail' => [
                    'AccountRef' => ['value' => '462'],         // Credit Card Fees
                ],
            ]],
        ]);

        $result = $client->Add($expense);
        $error  = $client->getLastError();

        if ($error) {
            throw new \RuntimeException('QB createFeeExpense error: ' . $error->getResponseBody());
        }

        return $result->Id ?? null;
    }

    public function createPayment(Payment $payment): ?string
    {
        $client = $this->getClient();
        $family = $payment->family;

        if (!$family->qb_customer_id) {
            return null;
        }

        $qbPayment = \QuickBooksOnline\API\Facades\Payment::create([
            'CustomerRef' => ['value' => $family->qb_customer_id],
            'TotalAmt'    => (float)$payment->amount,
            'TxnDate'     => $payment->payment_date->format('Y-m-d'),
            'PrivateNote' => $payment->notes ?? 'Portal payment',
        ]);

        $result = $client->Add($qbPayment);
        $error  = $client->getLastError();

        if ($error) {
            throw new \RuntimeException('QB createPayment error: ' . $error->getResponseBody());
        }

        return $result->Id ?? null;
    }

    public function createCustomer(Family $family): bool
    {
        $client = $this->getClient();

        $customerData = [
            'DisplayName' => $family->name,
        ];

        if ($family->phone) {
            $customerData['PrimaryPhone'] = ['FreeFormNumber' => $family->phone];
        }

        if ($family->address || $family->city) {
            $customerData['BillAddr'] = [
                'Line1'                  => $family->address,
                'City'                   => $family->city,
                'CountrySubDivisionCode' => $family->state,
                'PostalCode'             => $family->zip,
            ];
        }

        $customer = \QuickBooksOnline\API\Facades\Customer::create($customerData);
        $result   = $client->Add($customer);
        $error    = $client->getLastError();

        if ($error || !$result) return false;

        $family->update(['qb_customer_id' => $result->Id]);
        return true;
    }

    public function updateCustomer(Family $family): bool
    {
        if (!$family->qb_customer_id) return false;

        $client = $this->getClient();

        // Fetch existing to get SyncToken
        $existing = $client->FindById('Customer', $family->qb_customer_id);
        $error    = $client->getLastError();
        if ($error || !$existing) return false;

        $updated = \QuickBooksOnline\API\Facades\Customer::update($existing, [
            'sparse'       => 'true',
            'DisplayName'  => $family->name,
            'PrimaryPhone' => $family->phone ? ['FreeFormNumber' => $family->phone] : null,
            'BillAddr'     => [
                'Line1'                  => $family->address,
                'City'                   => $family->city,
                'CountrySubDivisionCode' => $family->state,
                'PostalCode'             => $family->zip,
            ],
        ]);

        $result = $client->Update($updated);
        $error  = $client->getLastError();

        return $error === null;
    }

    public function mapQbCustomerToFamily(object $customer, array $customerTypes = []): array
    {
        // CustomerTypeRef is a plain string ID in this SDK version
        $typeId         = (string)($customer->CustomerTypeRef ?? '');
        $typeName       = $typeId ? ($customerTypes[$typeId] ?? '') : '';
        $membershipType = MembershipType::fromQbCustomerType($typeName);

        // Email may be comma-separated — split into individual addresses
        $emailStr = $customer->PrimaryEmailAddr->Address ?? null;
        $emails   = $emailStr
            ? array_values(array_unique(array_filter(array_map('trim', explode(',', $emailStr)))))
            : [];

        return [
            'data' => [
                'name'                => $customer->DisplayName ?? $customer->CompanyName ?? 'Unknown',
                'phone'               => is_string($customer->PrimaryPhone ?? null)
                                            ? ($customer->PrimaryPhone ?: null)
                                            : ($customer->PrimaryPhone->FreeFormNumber ?? null),
                'address'             => $customer->BillAddr->Line1 ?? null,
                'city'                => $customer->BillAddr->City ?? null,
                'state'               => $customer->BillAddr->CountrySubDivisionCode ?? null,
                'zip'                 => $customer->BillAddr->PostalCode ?? null,
                'membership_type'     => $membershipType->value,
                'qb_customer_id'      => $customer->Id,
                'qb_sync_token'       => $customer->SyncToken ?? null,
                'outstanding_balance' => (float)($customer->Balance ?? 0),
            ],
            'emails' => array_values(array_unique($emails)),
        ];
    }

}
