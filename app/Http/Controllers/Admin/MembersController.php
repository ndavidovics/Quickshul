<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MembershipType;
use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Http\Request;

class MembersController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->buildQuery($request);

        $families = $query->orderBy('name')->paginate(30)->withQueryString();

        return view('admin.members.index', [
            'families'        => $families,
            'membershipTypes' => MembershipType::cases(),
        ]);
    }

    public function export(Request $request)
    {
        $families = $this->buildQuery($request)->orderBy('name')->get();

        return response()->streamDownload(function () use ($families) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Emails', 'Type', 'Phone', 'Address', 'City', 'State', 'ZIP', 'Outstanding Balance']);
            foreach ($families as $f) {
                $emails = $f->emails->pluck('email')->implode(', ');
                fputcsv($out, [
                    $f->name,
                    $emails,
                    $f->membership_type->label(),
                    $f->phone ?? '',
                    $f->address ?? '',
                    $f->city ?? '',
                    $f->state ?? '',
                    $f->zip ?? '',
                    number_format($f->outstanding_balance, 2),
                ]);
            }
            fclose($out);
        }, 'members.csv', ['Content-Type' => 'text/csv']);
    }

    private function buildQuery(Request $request)
    {
        $query = Family::with(['emails']);

        if ($search = $request->search) {
            $query->search($search);
        }

        if ($type = $request->membership_type) {
            $query->where('membership_type', $type);
        }

        if ($request->has_balance) {
            $query->withBalance();
        }

        return $query;
    }

    public function show(Request $request, int $id)
    {
        $family = Family::with(['emails', 'members', 'users'])
            ->withTrashed()
            ->findOrFail($id);

        $payments = $family->payments()->paginate(15, ['*'], 'pp')->withQueryString();
        $pledges  = $family->pledges()->paginate(15, ['*'], 'lp')->withQueryString();

        $auditLogs = \App\Models\AuditLog::where('auditable_type', Family::class)
            ->where('auditable_id', $id)
            ->latest('created_at')
            ->limit(30)
            ->get();

        return view('admin.members.show', compact('family', 'payments', 'pledges', 'auditLogs'));
    }

    public function exportPayments(int $id)
    {
        $family   = Family::withTrashed()->findOrFail($id);
        $payments = $family->payments()->get();

        $filename = 'payments-' . str($family->name)->slug() . '.csv';

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

    public function exportPledges(int $id)
    {
        $family  = Family::withTrashed()->findOrFail($id);
        $pledges = $family->pledges()->get();

        $filename = 'pledges-' . str($family->name)->slug() . '.csv';

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
}
