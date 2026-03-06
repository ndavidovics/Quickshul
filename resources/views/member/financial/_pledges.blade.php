@if($pledges->total() > 0)
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
@else
<p class="text-muted text-sm">No pledge history on record.</p>
@endif
