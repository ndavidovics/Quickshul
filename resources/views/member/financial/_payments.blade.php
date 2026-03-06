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
