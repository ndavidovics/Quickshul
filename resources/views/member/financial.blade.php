@extends('layouts.app')
@section('title', 'Financial')

@section('content')
<h1 class="page-title">Financial</h1>
<p class="page-subtitle">Payment history and balance</p>

@if(!$family)
    <div class="card"><p class="text-muted">No family account linked.</p></div>
@else

{{-- Balance summary --}}
<div class="grid-3" style="margin-bottom:1.5rem">
    <div class="stat-card">
        <div class="stat-label">Outstanding Balance</div>
        <div class="stat-value">${{ number_format($family->outstanding_balance, 2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Pledged</div>
        <div class="stat-value gold">${{ number_format($family->total_pledged, 2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Paid</div>
        <div class="stat-value gold">${{ number_format($family->total_paid, 2) }}</div>
    </div>
</div>

{{-- Pay Now --}}
@if($family->outstanding_balance > 0)
<div class="card" style="margin-bottom:1.5rem;border-color:rgba(201,168,76,0.4)">
    <div class="card-title">Pay Your Balance</div>
    <form method="POST" action="{{ route('financial.pay') }}" style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap">
        @csrf
        <div class="form-group" style="flex:1;min-width:200px;margin-bottom:0">
            <label class="form-label">Amount to Pay</label>
            <input type="number" name="amount" class="form-control"
                   value="{{ $family->outstanding_balance }}"
                   min="1" max="{{ $family->outstanding_balance }}"
                   step="0.01" required>
        </div>
        <button type="submit" class="btn btn-gold" style="height:40px">
            <img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/PP_logo_h_100x26.png" alt="PayPal" height="16" style="vertical-align:middle">
            &nbsp;Pay Now
        </button>
    </form>
</div>
@else
<div class="alert alert-success" style="margin-bottom:1.5rem">✓ Your account is current — no outstanding balance.</div>
@endif

{{-- Pledge / Invoice History --}}
@if($pledges->total() > 0)
<div class="card" style="margin-bottom:1.5rem">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem">
        <div class="card-title" style="margin-bottom:0">Pledge History</div>
        <a href="{{ route('financial.export.pledges') }}" class="btn btn-outline btn-sm" title="Export CSV">⬇ CSV</a>
    </div>
    <table class="table">
        <thead>
            <tr><th>Date</th><th>Description</th><th>Amount</th><th>Balance Due</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach($pledges as $pledge)
            <tr>
                <td class="text-sm text-muted">{{ $pledge->invoice_date->format('M j, Y') }}</td>
                <td class="text-sm">{{ $pledge->description ?: '—' }}</td>
                <td style="font-weight:600;color:var(--gold)">${{ number_format($pledge->amount, 2) }}</td>
                <td class="text-sm">
                    @if((float)$pledge->balance > 0)
                        <span style="color:var(--gold)">${{ number_format($pledge->balance, 2) }}</span>
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

{{-- Payment history --}}
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem">
        <div class="card-title" style="margin-bottom:0">Payment History</div>
        <a href="{{ route('financial.export.payments') }}" class="btn btn-outline btn-sm" title="Export CSV">⬇ CSV</a>
    </div>
    @if($payments->isEmpty())
        <p class="text-muted text-sm">No payments on record.</p>
    @else
    <table class="table">
        <thead>
            <tr><th>Date</th><th>Description</th><th>Amount</th><th>Reference</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach($payments as $p)
            <tr>
                <td class="text-sm">{{ $p->payment_date->format('M j, Y') }}</td>
                <td class="text-sm text-muted">{{ $p->description ?: '—' }}</td>
                <td style="font-weight:600;color:var(--gold)">${{ number_format($p->amount, 2) }}</td>
                <td class="text-muted text-sm" style="font-family:monospace;font-size:0.72rem">
                    {{ $p->qb_transaction_id ?? $p->qb_sales_receipt_id ?? $p->paypal_transaction_id ?? '—' }}
                </td>
                <td>
                    @if($p->status->value === 'completed')
                        <span class="badge badge-green">Completed</span>
                    @elseif($p->status->value === 'pending')
                        <span class="badge badge-muted">Pending</span>
                    @else
                        <span class="badge badge-red">Failed</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top:0.75rem">{{ $payments->links('vendor.pagination.simple-default') }}</div>
    @endif
</div>
@endif
@endsection
