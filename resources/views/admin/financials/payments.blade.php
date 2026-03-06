@extends('layouts.admin')
@section('title', 'Payments')

@section('content')
<div class="flex items-center justify-between" style="margin-bottom:1.5rem">
    <div>
        <h1 class="page-title">Payments</h1>
        <p class="page-subtitle" style="margin-bottom:0">{{ $payments->total() }} records</p>
    </div>
    <a href="{{ route('admin.financials.payments.export') }}?{{ http_build_query(request()->only(['search','method','date_range'])) }}"
       class="btn btn-outline">⬇ Export CSV</a>
</div>

<form method="GET" action="{{ route('admin.financials.payments') }}" style="margin-bottom:1.25rem">
    <div style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end">
        <div style="flex:2;min-width:200px">
            <input type="text" name="search" class="form-control" placeholder="Search family, description, transaction ID..." value="{{ request('search') }}">
        </div>
        <div style="min-width:160px">
            <select name="date_range" class="form-control">
                <option value="">All time</option>
                <option value="this_month" {{ request('date_range') === 'this_month' ? 'selected' : '' }}>This month</option>
                <option value="last_month" {{ request('date_range') === 'last_month' ? 'selected' : '' }}>Last month</option>
                <option value="this_year"  {{ request('date_range') === 'this_year'  ? 'selected' : '' }}>This year</option>
                <option value="last_year"  {{ request('date_range') === 'last_year'  ? 'selected' : '' }}>Last year</option>
                <option value="last_30"    {{ request('date_range') === 'last_30'    ? 'selected' : '' }}>Last 30 days</option>
                <option value="last_90"    {{ request('date_range') === 'last_90'    ? 'selected' : '' }}>Last 90 days</option>
                <option value="last_12m"   {{ request('date_range') === 'last_12m'   ? 'selected' : '' }}>Last 12 months</option>
            </select>
        </div>
        <div style="min-width:130px">
            <select name="method" class="form-control">
                <option value="">All methods</option>
                <option value="paypal" {{ request('method') === 'paypal' ? 'selected' : '' }}>PayPal</option>
                <option value="check"  {{ request('method') === 'check'  ? 'selected' : '' }}>Check</option>
                <option value="cash"   {{ request('method') === 'cash'   ? 'selected' : '' }}>Cash</option>
                <option value="ach"    {{ request('method') === 'ach'    ? 'selected' : '' }}>ACH</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if(request()->hasAny(['search','method','date_range']))
            <a href="{{ route('admin.financials.payments') }}" class="btn btn-outline">Clear</a>
        @endif
    </div>
</form>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Family</th>
                <th>Description</th>
                <th>Method</th>
                <th>Amount</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $p)
            <tr>
                <td class="text-sm text-muted" style="white-space:nowrap">{{ $p->payment_date->format('M j, Y') }}</td>
                <td>
                    @if($p->family)
                        <a href="{{ route('admin.members.show', $p->family_id) }}" style="color:var(--gold);text-decoration:none;font-size:0.85rem;font-weight:500">{{ $p->family->name }}</a>
                    @else
                        <span class="text-muted text-sm">—</span>
                    @endif
                </td>
                <td class="text-sm text-muted">{{ $p->description ?: '—' }}</td>
                <td>
                    @if($p->method)
                        <span class="badge badge-muted">{{ ucfirst($p->method) }}</span>
                    @else
                        <span class="text-muted text-sm">—</span>
                    @endif
                </td>
                <td style="font-weight:600;color:var(--gold);white-space:nowrap">${{ number_format($p->amount, 2) }}</td>
                <td class="text-muted" style="font-family:monospace;font-size:0.72rem">
                    {{ $p->qb_transaction_id ?? $p->qb_sales_receipt_id ?? $p->paypal_transaction_id ?? '—' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">No payments found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination">{{ $payments->links() }}</div>
</div>
@endsection
