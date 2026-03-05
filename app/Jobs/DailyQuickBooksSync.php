<?php

namespace App\Jobs;

use App\Enums\SyncDirection;
use App\Enums\SyncStatus;
use App\Models\Family;
use App\Models\FamilyEmail;
use App\Models\FamilyMember;
use App\Models\Payment;
use App\Models\Pledge;
use App\Models\QbConflict;
use App\Models\QbSyncLog;
use App\Models\User;
use App\Services\AuditService;
use App\Services\QuickBooksService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DailyQuickBooksSync implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;
    public int $tries   = 3;

    public function __construct(
        public readonly bool $forced = false,
        public readonly ?int $triggeredByUserId = null
    ) {}

    public function handle(QuickBooksService $qbService, AuditService $audit): void
    {
        if (!$qbService->isConnected()) {
            Log::warning('DailyQuickBooksSync: QB not connected, skipping.');
            return;
        }

        $log = QbSyncLog::create([
            'direction'            => SyncDirection::Pull->value,
            'status'               => SyncStatus::Pending->value,
            'triggered_by_user_id' => $this->triggeredByUserId,
        ]);

        $log->markRunning();

        $familiesProcessed = 0;
        $paymentsProcessed = 0;
        $conflictsFound    = 0;
        $errors            = [];

        try {
            $lastSync = QbSyncLog::where('direction', SyncDirection::Pull->value)
                ->where('status', SyncStatus::Completed->value)
                ->latest('completed_at')
                ->value('completed_at');

            $changedSince = (!$this->forced && $lastSync) ? Carbon::parse($lastSync) : null;

            // Load lookups once
            $customerTypes       = $qbService->getCustomerTypes();
            $itemMap             = $qbService->getItems();
            $defaultPasswordHash = Hash::make('yiom613!');

            // --- Sync Customers ---
            $customers = $qbService->getCustomers($changedSince);

            foreach ($customers as $customer) {
                try {
                    DB::transaction(function () use ($customer, $qbService, $customerTypes, $defaultPasswordHash, &$familiesProcessed, &$conflictsFound) {
                        $mapped = $qbService->mapQbCustomerToFamily($customer, $customerTypes);
                        $data   = $mapped['data'];
                        $emails = $mapped['emails'];

                        $existing = Family::withTrashed()
                            ->where('qb_customer_id', $data['qb_customer_id'])
                            ->first();

                        if ($existing) {
                            $conflicts = $this->detectConflicts($existing, $data);
                            $conflictsFound += count($conflicts);
                            foreach ($conflicts as $field => [$portalVal, $qbVal]) {
                                QbConflict::firstOrCreate(
                                    ['entity_type' => 'family', 'entity_id' => $existing->id, 'field' => $field, 'resolved' => false],
                                    [
                                        'portal_value'      => $portalVal,
                                        'portal_updated_at' => $existing->updated_at,
                                        'qb_value'          => $qbVal,
                                        'qb_updated_at'     => now(),
                                    ]
                                );
                            }
                            $safeUpdate = array_filter(
                                array_diff_key($data, $conflicts),
                                fn($val, $field) => !($val === null && isset($existing->$field) && $existing->$field !== null),
                                ARRAY_FILTER_USE_BOTH
                            );
                            $existing->update($safeUpdate);
                        } else {
                            $existing = Family::create($data);
                        }

                        foreach ($emails as $index => $email) {
                            if (!$email) continue;

                            FamilyEmail::firstOrCreate(
                                ['email' => $email],
                                ['family_id' => $existing->id, 'is_primary' => $index === 0]
                            );

                            if (!User::where('email', $email)->exists()) {
                                User::create([
                                    'name'      => $existing->name,
                                    'email'     => $email,
                                    'password'  => $defaultPasswordHash,
                                    'family_id' => $existing->id,
                                ]);
                            } else {
                                User::where('email', $email)
                                    ->whereNull('family_id')
                                    ->update(['family_id' => $existing->id]);
                            }
                        }

                        // Auto-create family members from name if none exist yet
                        if ($existing->members()->count() === 0) {
                            foreach ($this->parseFamilyNameToMembers($existing->name) as $m) {
                                FamilyMember::create(array_merge($m, ['family_id' => $existing->id]));
                            }
                        }

                        $familiesProcessed++;
                    });
                } catch (\Throwable $e) {
                    $errors[] = ['customer' => $customer->Id ?? '?', 'error' => $e->getMessage()];
                    Log::error('QB sync customer error: ' . $e->getMessage());
                }
            }

            // --- Sync Invoices (Pledges) ---
            // Build invoice map: qb_invoice_id → invoice data (also used for payment descriptions)
            $invoiceMap = $qbService->getInvoices($itemMap, $changedSince);

            // Also build pledge totals per customer from invoice map
            $pledgeTotals = [];
            foreach ($invoiceMap as $inv) {
                $custId = $inv['qb_customer_id'];
                $pledgeTotals[$custId] = ($pledgeTotals[$custId] ?? 0) + $inv['amount'];
            }

            foreach ($invoiceMap as $inv) {
                try {
                    $family = Family::where('qb_customer_id', $inv['qb_customer_id'])->first();
                    if (!$family) continue;

                    Pledge::updateOrCreate(
                        ['qb_invoice_id' => $inv['qb_invoice_id']],
                        [
                            'family_id'    => $family->id,
                            'description'  => $inv['description'],
                            'amount'       => $inv['amount'],
                            'balance'      => $inv['balance'],
                            'invoice_date' => $inv['invoice_date'],
                            'due_date'     => $inv['due_date'],
                            'status'       => $inv['status'],
                        ]
                    );
                } catch (\Throwable $e) {
                    $errors[] = ['invoice' => $inv['qb_invoice_id'], 'error' => $e->getMessage()];
                    Log::error('QB sync invoice error: ' . $e->getMessage());
                }
            }

            // Update total_pledged on each family from invoice totals
            foreach ($pledgeTotals as $custId => $total) {
                Family::where('qb_customer_id', $custId)->update(['total_pledged' => $total]);
            }

            // --- Sync Payments (QB Payment entities) ---
            $transactions = $qbService->getTransactions($changedSince);

            foreach ($transactions as $transaction) {
                try {
                    DB::transaction(function () use ($transaction, $invoiceMap, &$paymentsProcessed) {
                        $txnId  = $transaction->Id ?? null;
                        $custId = (string)($transaction->CustomerRef ?? '');
                        $amount = (float)($transaction->TotalAmt ?? 0);
                        $date   = Carbon::parse($transaction->TxnDate ?? now());

                        if (!$custId || !$txnId) return;

                        $family = Family::where('qb_customer_id', $custId)->first();
                        if (!$family) return;

                        // Build description from linked invoice(s)
                        $desc = $this->buildPaymentDescription($transaction, $invoiceMap);

                        // If a PayPal payment for the same family/amount/date already exists,
                        // link it to this QB transaction rather than creating a duplicate row.
                        $portal = Payment::where('family_id', $family->id)
                            ->where('amount', $amount)
                            ->whereDate('payment_date', $date->toDateString())
                            ->whereNotNull('paypal_transaction_id')
                            ->whereNull('qb_transaction_id')
                            ->first();

                        if ($portal) {
                            $portal->update(['qb_transaction_id' => $txnId]);
                        } else {
                            Payment::updateOrCreate(
                                ['qb_transaction_id' => $txnId],
                                [
                                    'family_id'    => $family->id,
                                    'amount'       => $amount,
                                    'payment_date' => $date,
                                    'method'       => 'quickbooks',
                                    'description'  => $desc,
                                    'status'       => 'completed',
                                ]
                            );
                        }

                        $paymentsProcessed++;
                    });
                } catch (\Throwable $e) {
                    $errors[] = ['transaction' => $transaction->Id ?? '?', 'error' => $e->getMessage()];
                    Log::error('QB sync transaction error: ' . $e->getMessage());
                }
            }

            // --- Sync Sales Receipts ---
            $salesReceipts = $qbService->getSalesReceipts($itemMap, $changedSince);

            foreach ($salesReceipts as $sr) {
                try {
                    $family = Family::where('qb_customer_id', $sr['qb_customer_id'])->first();
                    if (!$family) continue;

                    // Same dedup check for sales receipts
                    $portal = Payment::where('family_id', $family->id)
                        ->where('amount', $sr['amount'])
                        ->whereDate('payment_date', Carbon::parse($sr['payment_date'])->toDateString())
                        ->whereNotNull('paypal_transaction_id')
                        ->whereNull('qb_sales_receipt_id')
                        ->first();

                    if ($portal) {
                        $portal->update(['qb_sales_receipt_id' => $sr['qb_sales_receipt_id']]);
                    } else {
                        Payment::updateOrCreate(
                            ['qb_sales_receipt_id' => $sr['qb_sales_receipt_id']],
                            [
                                'family_id'    => $family->id,
                                'amount'       => $sr['amount'],
                                'payment_date' => $sr['payment_date'],
                                'method'       => 'quickbooks',
                                'description'  => $sr['description'],
                                'status'       => 'completed',
                            ]
                        );
                    }

                    $paymentsProcessed++;
                } catch (\Throwable $e) {
                    $errors[] = ['sales_receipt' => $sr['qb_sales_receipt_id'], 'error' => $e->getMessage()];
                    Log::error('QB sync sales receipt error: ' . $e->getMessage());
                }
            }

            $log->markCompleted($familiesProcessed, $paymentsProcessed, $conflictsFound);
            if ($errors) {
                $log->update(['errors' => $errors]);
            }

            $audit->log('admin.qb.sync.pull', null, [], [], "QB pull sync completed: {$familiesProcessed} families, {$paymentsProcessed} payments, {$conflictsFound} conflicts");

        } catch (\Throwable $e) {
            $errors[] = ['fatal' => $e->getMessage()];
            $log->markFailed($errors);
            Log::error('DailyQuickBooksSync fatal error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build a payment description by looking up the invoices it was applied to.
     */
    private function buildPaymentDescription(object $transaction, array $invoiceMap): string
    {
        $raw = $transaction->Line ?? null;
        if (!$raw) return '';

        // Single line item comes as an object, multiple as an array — never use (array) cast
        $lines = is_array($raw) ? $raw : [$raw];

        $descs = [];
        foreach ($lines as $line) {
            $linkedTxn = $line->LinkedTxn ?? null;
            if (!$linkedTxn) continue;

            // LinkedTxn is a single object or an array of objects — never cast to (array)
            $links = is_array($linkedTxn) ? $linkedTxn : [$linkedTxn];
            foreach ($links as $link) {
                if (($link->TxnType ?? '') !== 'Invoice') continue;
                $invoiceId = (string)($link->TxnId ?? '');
                if (!$invoiceId) continue;

                // Check in-memory map first (covers full sync and recently changed invoices)
                if (isset($invoiceMap[$invoiceId])) {
                    $d = trim($invoiceMap[$invoiceId]['description'] ?? '');
                } else {
                    // Fall back to DB for invoices not in the current sync window
                    $d = trim(Pledge::where('qb_invoice_id', $invoiceId)->value('description') ?? '');
                }

                if ($d && !in_array($d, $descs)) {
                    $descs[] = $d;
                }
            }
        }

        return implode('; ', $descs);
    }

    /**
     * Parse a QB family display name into individual FamilyMember records.
     * Handles patterns like:
     *   "Davidovics Noam & Deena"  → [Noam Davidovics, Deena Davidovics]
     *   "Cohen David & Rachel"      → [David Cohen, Rachel Cohen]
     *   "Smith John"                → [John Smith]
     *   "Levy Family"               → [Levy (single adult)]
     */
    public function parseFamilyNameToMembers(string $familyName): array
    {
        // Strip trailing "Family" suffix
        $name = trim(preg_replace('/\s+Family\s*$/i', '', $familyName));
        if (!$name) return [];

        $sharedLastName = '';
        $firstNames     = [];

        // Pattern A: "LastName, FirstName & SecondName" or "LastName, FirstName and SecondName"
        if (str_contains($name, ',')) {
            [$lastName, $rest] = array_map('trim', explode(',', $name, 2));
            $sharedLastName = $lastName;
            // Split the first-name portion on & or and
            $firstNames = array_map('trim', preg_split('/\s+&\s+|\s+and\s+/i', $rest));
        } else {
            // Pattern B: "LastName FirstName & SecondName" (no comma)
            $parts = array_map('trim', preg_split('/\s+&\s+|\s+and\s+/i', $name));

            // First part: first word = last name, rest = first name
            $words = preg_split('/\s+/', $parts[0], -1, PREG_SPLIT_NO_EMPTY);
            if (count($words) === 1) {
                $sharedLastName = $words[0];
                $firstNames[]   = ''; // no first name parseable
            } else {
                $sharedLastName = $words[0];
                $firstNames[]   = implode(' ', array_slice($words, 1));
            }

            // Remaining parts
            foreach (array_slice($parts, 1) as $part) {
                $words    = preg_split('/\s+/', $part, -1, PREG_SPLIT_NO_EMPTY);
                $lastWord = end($words);
                if (count($words) === 1) {
                    $firstNames[] = $words[0];
                } elseif (strcasecmp($lastWord, $sharedLastName) === 0) {
                    $firstNames[] = implode(' ', array_slice($words, 0, -1));
                } else {
                    // Different last name — treat as "FirstName LastName", override shared
                    $firstNames[] = implode(' ', array_slice($words, 0, -1)) . '|' . $lastWord;
                }
            }
        }

        $members = [];
        foreach ($firstNames as $fn) {
            // Handle overridden last name encoded as "firstName|lastName"
            if (str_contains($fn, '|')) {
                [$fn, $ln] = explode('|', $fn, 2);
            } else {
                $ln = $sharedLastName;
            }
            $fn = trim($fn);
            $ln = trim($ln);
            if ($fn === '' && $ln === '') continue;
            $members[] = [
                'first_name' => $fn ?: $ln,
                'last_name'  => $fn ? $ln : '',
                'role'       => 'parent',
                'gender'     => 'other',
            ];
        }

        return $members;
    }

    private function detectConflicts(Family $existing, array $incomingData): array
    {
        $checkFields = ['name', 'phone', 'address', 'city', 'state', 'zip'];
        $conflicts   = [];

        foreach ($checkFields as $field) {
            if (!array_key_exists($field, $incomingData)) continue;

            $portalVal = $existing->$field;
            $qbVal     = $incomingData[$field];

            if ($portalVal !== $qbVal
                && $qbVal !== null
                && $existing->updated_at?->gt($existing->qb_last_sync_at ?? Carbon::createFromDate(2000, 1, 1))) {
                $conflicts[$field] = [$portalVal, $qbVal];
            }
        }

        return $conflicts;
    }
}
