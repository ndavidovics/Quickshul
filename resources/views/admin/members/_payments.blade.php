@forelse($payments as $p)
<tr>
    <td class="text-sm">{{ $p->payment_date->format('M j, Y') }}</td>
    <td class="text-sm text-muted">{{ $p->description ?: '—' }}</td>
    <td style="color:var(--gold);font-weight:600">${{ number_format($p->amount,2) }}</td>
    <td class="text-muted text-sm" style="font-family:monospace;font-size:0.72rem">{{ $p->qb_transaction_id ?? $p->qb_sales_receipt_id ?? $p->paypal_transaction_id ?? '—' }}</td>
</tr>
@empty
<tr><td colspan="4" style="color:var(--text-muted);text-align:center;padding:1rem">No payments on record.</td></tr>
@endforelse
@if($payments instanceof \Illuminate\Pagination\LengthAwarePaginator && $payments->hasPages())
<tr><td colspan="4" style="padding:0.75rem 0">{{ $payments->links('vendor.pagination.simple-default') }}</td></tr>
@endif
