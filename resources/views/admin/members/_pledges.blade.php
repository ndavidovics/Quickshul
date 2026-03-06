@forelse($pledges as $pledge)
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
@empty
<tr><td colspan="5" style="color:var(--text-muted);text-align:center;padding:1rem">No pledges on record.</td></tr>
@endforelse
@if($pledges instanceof \Illuminate\Pagination\LengthAwarePaginator && $pledges->hasPages())
<tr><td colspan="5" style="padding:0.75rem 0">{{ $pledges->links('vendor.pagination.simple-default') }}</td></tr>
@endif
