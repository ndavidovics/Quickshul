<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Pledge;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinancialsController extends Controller
{
    private function applyDateRange($query, string $dateColumn, ?string $range): void
    {
        match($range) {
            'this_month'  => $query->whereBetween($dateColumn, [now()->startOfMonth(), now()->endOfMonth()]),
            'last_month'  => $query->whereBetween($dateColumn, [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]),
            'this_year'   => $query->whereBetween($dateColumn, [now()->startOfYear(), now()->endOfYear()]),
            'last_year'   => $query->whereBetween($dateColumn, [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()]),
            'last_30'     => $query->where($dateColumn, '>=', now()->subDays(30)),
            'last_90'     => $query->where($dateColumn, '>=', now()->subDays(90)),
            'last_12m'    => $query->where($dateColumn, '>=', now()->subMonths(12)),
            default       => null,
        };
    }

    public function payments(Request $request)
    {
        $query = Payment::with('family')
            ->completed()
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('family', fn($fq) => $fq->where('name', 'like', "%{$search}%"))
                  ->orWhere('qb_transaction_id', 'like', "%{$search}%")
                  ->orWhere('qb_sales_receipt_id', 'like', "%{$search}%")
                  ->orWhere('paypal_transaction_id', 'like', "%{$search}%");
            });
        }

        if ($method = $request->method) {
            $query->where('method', $method);
        }

        $this->applyDateRange($query, 'payment_date', $request->date_range);

        $payments = $query->paginate(50)->withQueryString();

        return view('admin.financials.payments', compact('payments'));
    }

    public function exportPayments(Request $request)
    {
        $query = Payment::with('family')
            ->completed()
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('family', fn($fq) => $fq->where('name', 'like', "%{$search}%"))
                  ->orWhere('qb_transaction_id', 'like', "%{$search}%")
                  ->orWhere('qb_sales_receipt_id', 'like', "%{$search}%")
                  ->orWhere('paypal_transaction_id', 'like', "%{$search}%");
            });
        }

        if ($method = $request->method) {
            $query->where('method', $method);
        }

        $this->applyDateRange($query, 'payment_date', $request->date_range);

        $payments = $query->get();

        return response()->streamDownload(function () use ($payments) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Family', 'Description', 'Amount', 'Method', 'QB Txn ID', 'QB Receipt ID', 'PayPal Txn ID']);
            foreach ($payments as $p) {
                fputcsv($out, [
                    $p->payment_date->format('Y-m-d'),
                    $p->family?->name ?? '',
                    $p->description ?? '',
                    number_format($p->amount, 2),
                    $p->method ?? '',
                    $p->qb_transaction_id ?? '',
                    $p->qb_sales_receipt_id ?? '',
                    $p->paypal_transaction_id ?? '',
                ]);
            }
            fclose($out);
        }, 'payments.csv', ['Content-Type' => 'text/csv']);
    }

    public function pledges(Request $request)
    {
        $query = Pledge::with('family')
            ->orderByDesc('invoice_date')
            ->orderByDesc('id');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('family', fn($fq) => $fq->where('name', 'like', "%{$search}%"))
                  ->orWhere('qb_invoice_id', 'like', "%{$search}%");
            });
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $this->applyDateRange($query, 'invoice_date', $request->date_range);

        $pledges = $query->paginate(50)->withQueryString();

        return view('admin.financials.pledges', compact('pledges'));
    }

    public function exportPledges(Request $request)
    {
        $query = Pledge::with('family')
            ->orderByDesc('invoice_date')
            ->orderByDesc('id');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('family', fn($fq) => $fq->where('name', 'like', "%{$search}%"))
                  ->orWhere('qb_invoice_id', 'like', "%{$search}%");
            });
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $this->applyDateRange($query, 'invoice_date', $request->date_range);

        $pledges = $query->get();

        return response()->streamDownload(function () use ($pledges) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Invoice Date', 'Due Date', 'Family', 'Description', 'Amount', 'Balance Due', 'Status', 'QB Invoice ID']);
            foreach ($pledges as $p) {
                fputcsv($out, [
                    $p->invoice_date->format('Y-m-d'),
                    $p->due_date?->format('Y-m-d') ?? '',
                    $p->family?->name ?? '',
                    $p->description ?? '',
                    number_format($p->amount, 2),
                    number_format($p->balance, 2),
                    $p->status,
                    $p->qb_invoice_id ?? '',
                ]);
            }
            fclose($out);
        }, 'pledges.csv', ['Content-Type' => 'text/csv']);
    }
}
