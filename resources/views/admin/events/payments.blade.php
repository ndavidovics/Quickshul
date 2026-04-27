@extends('layouts.admin')
@section('title', $event->name . ' — Payments')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $event->name }}</h1>
        <p class="page-subtitle">
            Payment report
            @if($event->event_date) · {{ $event->event_date->format('F j, Y') }} @endif
            · <span class="badge {{ $event->status === 'active' ? 'badge-success' : 'badge-muted' }}">{{ ucfirst($event->status) }}</span>
        </p>
    </div>
    <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">← Events</a>
</div>

<div class="stats-grid" style="margin-bottom:1.5rem">
    <div class="stat-card">
        <div class="stat-value">{{ $payments->count() }}</div>
        <div class="stat-label">Completed Payments</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">${{ number_format($totalRevenue, 2) }}</div>
        <div class="stat-label">Total Revenue</div>
    </div>
    @php
        $ticketTotals = [];
        foreach ($payments as $p) {
            foreach ($p->ticket_quantities as $typeId => $qty) {
                $ticketTotals[$typeId] = ($ticketTotals[$typeId] ?? 0) + $qty;
            }
        }
        $ticketMap = $event->ticketTypes->keyBy('id');
    @endphp
    @foreach($ticketMap as $type)
    <div class="stat-card">
        <div class="stat-value">{{ $ticketTotals[$type->id] ?? 0 }}</div>
        <div class="stat-label">{{ $type->name }} Tickets</div>
    </div>
    @endforeach
</div>

@if($payments->isEmpty())
    <div class="empty-state">
        <p>No payments yet for this event.</p>
    </div>
@else
    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Payer</th>
                    <th>Tickets</th>
                    <th>Total</th>
                    <th>Member</th>
                    <th>QB</th>
                    <th>Transaction</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $p)
                <tr>
                    <td style="white-space:nowrap">{{ $p->created_at->format('M j, Y g:ia') }}</td>
                    <td>
                        <div>{{ $p->payer_name }}</div>
                        <div style="font-size:0.75rem;color:var(--text-muted)">{{ $p->payer_email }}</div>
                    </td>
                    <td>
                        @foreach($p->ticket_quantities as $typeId => $qty)
                            @if($ticketMap->has($typeId) && $qty > 0)
                                <div style="font-size:0.8rem">{{ $qty }}× {{ $ticketMap[$typeId]->name }}</div>
                            @endif
                        @endforeach
                    </td>
                    <td>${{ number_format($p->total_amount, 2) }}</td>
                    <td>
                        @if($p->family)
                            <a href="{{ route('admin.members.show', $p->family->id) }}" style="color:var(--gold)">
                                {{ $p->family->name }}
                            </a>
                        @else
                            <span style="color:var(--text-muted)">—</span>
                        @endif
                    </td>
                    <td>
                        @if($p->qb_sales_receipt_id)
                            <span class="badge badge-success" title="QB Receipt #{{ $p->qb_sales_receipt_id }}">Synced</span>
                        @elseif($p->family_id)
                            <span style="color:var(--text-muted);font-size:0.8rem">Pending</span>
                        @else
                            <span style="color:var(--text-muted);font-size:0.8rem">—</span>
                        @endif
                    </td>
                    <td style="font-size:0.75rem;color:var(--text-muted)">
                        {{ $p->paypal_transaction_id ? substr($p->paypal_transaction_id, 0, 12) . '…' : '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
