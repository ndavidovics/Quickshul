@extends('layouts.admin')
@section('title', $family->name)

@section('content')
<div class="flex items-center gap-3" style="margin-bottom:1.5rem;flex-wrap:wrap">
    <a href="{{ route('admin.members') }}" class="btn btn-outline btn-sm">← Members</a>
    <h1 class="page-title" style="margin-bottom:0">{{ $family->name }}</h1>
    <a href="{{ route('admin.members.edit', $family->id) }}" class="btn btn-primary btn-sm">Edit</a>
    @if($family->outstanding_balance > 0)
    <a href="{{ route('admin.members.email', $family->id) }}" class="btn btn-gold btn-sm">✉ Send Balance Email</a>
    @endif
    @if($family->qb_customer_id)
    <form method="POST" action="{{ route('admin.members.push-to-qb', $family->id) }}" style="margin:0">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm" title="Push changes to QuickBooks">↑ Push to QB</button>
    </form>
    @endif
</div>

<div class="grid-2">
    {{-- Family info --}}
    <div class="card">
        <div class="card-title">Family Details</div>
        <table style="width:100%;font-size:0.875rem;border-collapse:collapse">
            @foreach([
                'Membership Type' => $family->membership_type->label(),
                'Member Since'    => $family->member_since?->format('F j, Y') ?? '—',
                'Phone'           => $family->phone ?? '—',
                'Address'         => trim(($family->address??'').', '.($family->city??'').($family->state?', '.$family->state:'').($family->zip?' '.$family->zip:''), ', '),
            ] as $label => $value)
            <tr>
                <td style="padding:0.5rem 0;color:var(--text-muted);width:40%">{{ $label }}</td>
                <td style="padding:0.5rem 0;font-weight:500">{{ $value }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    {{-- Financial --}}
    <div class="card">
        <div class="card-title">Financials</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div class="stat-card"><div class="stat-label">Pledged</div><div class="stat-value gold">${{ number_format($family->total_pledged,2) }}</div></div>
            <div class="stat-card"><div class="stat-label">Paid</div><div class="stat-value">${{ number_format($family->total_paid,2) }}</div></div>
            <div class="stat-card" style="grid-column:1/-1"><div class="stat-label">Outstanding</div><div class="stat-value {{ $family->outstanding_balance > 0 ? '' : 'gold' }}">${{ number_format($family->outstanding_balance,2) }}</div></div>
        </div>
    </div>
</div>

{{-- Emails --}}
<div class="card" style="margin-top:1.25rem">
    <div class="card-title">Login Emails</div>
    @foreach($family->emails as $e)
    <div style="display:flex;align-items:center;gap:0.75rem;padding:0.4rem 0;border-bottom:1px solid var(--border-dim)">
        <span>{{ $e->email }}</span>
        @if($e->is_primary) <span class="badge badge-gold">Primary</span> @endif
    </div>
    @endforeach
</div>

{{-- Members --}}
<div class="card" style="margin-top:1.25rem">
    <div class="card-title">Family Members</div>
    <table class="table">
        <thead><tr><th>Name</th><th>Hebrew Name</th><th>Role</th><th>DOB</th><th>Hebrew DOB</th><th></th></tr></thead>
        <tbody>
            @foreach($family->members as $m)
            <tr>
                <td><span style="font-weight:500">{{ $m->full_name }}</span> @if($m->isDeceased()) <span class="badge badge-muted">†</span> @endif</td>
                <td style="font-size:0.95rem;direction:rtl">{{ $m->hebrew_name ?? '—' }}</td>
                <td><span class="badge badge-muted">{{ ucfirst($m->role) }}</span></td>
                <td class="text-muted text-sm">{{ $m->date_of_birth?->format('M j, Y') ?? '—' }}</td>
                <td class="text-sm">{{ $m->hebrew_date_of_birth ?? '—' }}</td>
                <td><a href="{{ route('admin.members.edit-member', [$family->id, $m->id]) }}" class="btn btn-outline btn-sm">Edit</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Pledges --}}
@if($pledges->total() > 0)
<div class="card" style="margin-top:1.25rem">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem">
        <div class="card-title" style="margin-bottom:0">Pledge / Invoice History</div>
        <a href="{{ route('admin.members.export.pledges', $family->id) }}" class="btn btn-outline btn-sm" title="Export CSV">⬇ CSV</a>
    </div>
    <table class="table">
        <thead><tr><th>Date</th><th>Description</th><th>Amount</th><th>Balance Due</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($pledges as $pledge)
            <tr>
                <td class="text-sm text-muted">{{ $pledge->invoice_date->format('M j, Y') }}</td>
                <td class="text-sm">{{ $pledge->description ?: '—' }}</td>
                <td style="color:var(--gold);font-weight:600">${{ number_format($pledge->amount,2) }}</td>
                <td class="text-sm">
                    @if((float)$pledge->balance > 0)
                        <span style="color:var(--gold)">${{ number_format($pledge->balance,2) }}</span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if($pledge->status === 'paid')
                        <span class="badge badge-green">Paid</span>
                    @elseif($pledge->status === 'voided')
                        <span class="badge badge-muted">Voided</span>
                    @else
                        <span class="badge badge-muted">Open</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top:0.75rem">{{ $pledges->links('vendor.pagination.simple-default') }}</div>
</div>
@endif

{{-- Payments --}}
<div class="card" style="margin-top:1.25rem">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem">
        <div class="card-title" style="margin-bottom:0">Payment History</div>
        <a href="{{ route('admin.members.export.payments', $family->id) }}" class="btn btn-outline btn-sm" title="Export CSV">⬇ CSV</a>
    </div>
    <table class="table">
        <thead><tr><th>Date</th><th>Description</th><th>Amount</th><th>Reference</th><th>Status</th></tr></thead>
        <tbody>
            @forelse($payments as $p)
            <tr>
                <td class="text-sm">{{ $p->payment_date->format('M j, Y') }}</td>
                <td class="text-sm text-muted">{{ $p->description ?: '—' }}</td>
                <td style="color:var(--gold);font-weight:600">${{ number_format($p->amount,2) }}</td>
                <td class="text-muted text-sm" style="font-family:monospace;font-size:0.72rem">{{ $p->qb_transaction_id ?? $p->qb_sales_receipt_id ?? $p->paypal_transaction_id ?? '—' }}</td>
                <td><span class="badge {{ $p->status->value==='completed'?'badge-green':($p->status->value==='pending'?'badge-muted':'badge-red') }}">{{ $p->status->label() }}</span></td>
            </tr>
            @empty
            <tr><td colspan="5" style="color:var(--text-muted);text-align:center;padding:1rem">No payments on record.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:0.75rem">{{ $payments->links('vendor.pagination.simple-default') }}</div>
</div>

{{-- Audit log --}}
@if($auditLogs->count())
<div class="card" style="margin-top:1.25rem">
    <div class="card-title">Audit Log</div>
    <table class="table">
        <thead><tr><th>When</th><th>Action</th><th>By</th><th>Description</th></tr></thead>
        <tbody>
            @foreach($auditLogs as $log)
            <tr>
                <td class="text-muted text-sm">{{ $log->created_at->format('M j, Y g:i a') }}</td>
                <td><span class="badge badge-muted">{{ $log->action }}</span></td>
                <td class="text-muted text-sm">{{ $log->user?->name ?? 'System' }}</td>
                <td class="text-sm">{{ $log->description }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
