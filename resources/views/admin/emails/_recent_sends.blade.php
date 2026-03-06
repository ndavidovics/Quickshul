@forelse($recentSends as $send)
<tr>
    <td class="text-sm text-muted">{{ $send->created_at->format('M j, Y g:i a') }} <span style="font-size:0.7rem;opacity:0.6">CST</span></td>
    <td class="text-sm">{{ $send->family?->name ?? '—' }}</td>
    <td class="text-sm text-muted" style="font-size:0.78rem">
        {{ $send->recipient_email }}
        @if($send->family)
            @foreach($send->family->emails->where('is_primary', false)->where('email', '!=', $send->recipient_email) as $cc)
                <br><span style="opacity:0.65">CC: {{ $cc->email }}</span>
            @endforeach
        @endif
    </td>
    <td class="text-sm" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $send->subject }}</td>
    <td>
        @if($send->status->value === 'sent')
            <span class="badge badge-green">Sent</span>
        @elseif($send->status->value === 'pending')
            <span class="badge badge-muted">Pending</span>
        @else
            <span class="badge badge-red" title="{{ $send->error }}">Failed</span>
        @endif
    </td>
</tr>
@empty
<tr><td colspan="5" style="color:var(--text-muted);text-align:center;padding:1rem">No emails sent yet.</td></tr>
@endforelse
@if($recentSends instanceof \Illuminate\Pagination\LengthAwarePaginator && $recentSends->hasPages())
<tr><td colspan="5" style="padding:0.75rem 0">{{ $recentSends->links('vendor.pagination.simple-default') }}</td></tr>
@endif
