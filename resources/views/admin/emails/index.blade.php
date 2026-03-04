@extends('layouts.admin')
@section('title', 'Email Reminders')

@section('content')
<div class="flex items-center justify-between" style="margin-bottom:1.5rem">
    <div>
        <h1 class="page-title">Email Reminders</h1>
        <p class="page-subtitle" style="margin-bottom:0">Send payment reminders and announcements to members</p>
    </div>
</div>

@if(session('success'))
<div style="background:rgba(100,200,120,0.12);border:1px solid rgba(100,200,120,0.4);border-radius:6px;padding:0.875rem 1rem;margin-bottom:1.25rem;color:#7ecf8e;font-size:0.875rem">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div style="background:rgba(240,128,128,0.1);border:1px solid rgba(240,128,128,0.4);border-radius:6px;padding:0.875rem 1rem;margin-bottom:1.25rem;color:#f08080;font-size:0.875rem">
    @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
</div>
@endif

<div class="grid-2">

{{-- Send panel --}}
<div class="card">
    <div class="card-title">Send Email</div>

    @if($templates->isEmpty())
    <div style="color:var(--text-muted);font-size:0.875rem;padding:1rem 0">
        No email templates found. Add templates to the <code style="font-size:0.75rem;background:rgba(255,255,255,0.08);padding:0.1rem 0.3rem;border-radius:3px">email_templates</code> table to get started.
    </div>
    @else
    <form method="POST" action="{{ route('admin.emails.send') }}" id="send-form">
        @csrf

        <div class="form-group">
            <label class="form-label">Template</label>
            <select name="template_id" class="form-control" id="template-select">
                <option value="">— Select a template —</option>
                @foreach($templates as $t)
                <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Recipients</label>
            <div style="display:flex;flex-direction:column;gap:0.5rem">
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.875rem">
                    <input type="radio" name="recipient_mode" value="all_with_balance" checked>
                    All families with outstanding balance ({{ $families->count() }})
                </label>
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.875rem">
                    <input type="radio" name="recipient_mode" value="selected">
                    Select specific families
                </label>
            </div>
        </div>

        <div id="family-select-panel" style="display:none">
            <div class="form-group">
                <label class="form-label">Select Families</label>
                <div style="border:1px solid var(--border-dim);border-radius:6px;max-height:220px;overflow-y:auto;padding:0.5rem">
                    @foreach($families as $f)
                    <label style="display:flex;align-items:center;gap:0.5rem;padding:0.3rem 0.4rem;cursor:pointer;font-size:0.85rem;border-radius:4px" class="hover-row">
                        <input type="checkbox" name="family_ids[]" value="{{ $f->id }}">
                        <span style="flex:1">{{ $f->name }}</span>
                        <span style="color:var(--gold);font-size:0.78rem">${{ number_format($f->outstanding_balance, 2) }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div style="margin-top:1rem;display:flex;gap:0.75rem;flex-wrap:wrap">
            <button type="submit" class="btn btn-gold">Send Emails</button>
            <button type="button" class="btn btn-outline" id="preview-btn" disabled>Preview</button>
        </div>
    </form>
    @endif
</div>

{{-- Template viewer --}}
<div class="card" id="preview-panel">
    <div class="card-title">Template Preview</div>
    <div id="preview-content" style="color:var(--text-muted);font-size:0.875rem;min-height:120px">
        Select a template and family to preview the rendered email.
    </div>
</div>

</div>

{{-- Recent sends --}}
@if($recentSends->count())
<div class="card" style="margin-top:1.25rem">
    <div class="card-title">Recent Sends</div>
    <table class="table">
        <thead>
            <tr><th>Sent</th><th>Family</th><th>Recipient</th><th>Subject</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach($recentSends as $send)
            <tr>
                <td class="text-sm text-muted">{{ $send->created_at->format('M j, Y g:i a') }}</td>
                <td class="text-sm">{{ $send->family?->name ?? '—' }}</td>
                <td class="text-sm text-muted" style="font-size:0.78rem">{{ $send->recipient_email }}</td>
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
            @endforeach
        </tbody>
    </table>
</div>
@endif

<script>
(function() {
    const radios = document.querySelectorAll('input[name="recipient_mode"]');
    const panel  = document.getElementById('family-select-panel');
    const previewBtn = document.getElementById('preview-btn');
    const templateSelect = document.getElementById('template-select');
    const previewContent = document.getElementById('preview-content');

    radios.forEach(r => r.addEventListener('change', () => {
        panel.style.display = r.value === 'selected' && r.checked ? 'block' : 'none';
    }));

    if (templateSelect) {
        templateSelect.addEventListener('change', () => {
            previewBtn.disabled = !templateSelect.value;
        });
    }

    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            const templateId = templateSelect.value;
            const firstChecked = document.querySelector('input[name="family_ids[]"]:checked');
            const firstFamily  = document.querySelector('input[name="family_ids[]"]');
            const familyId = firstChecked?.value || firstFamily?.value;

            if (!templateId || !familyId) {
                previewContent.innerHTML = '<span style="color:#f08080">Please select a template and at least one family.</span>';
                return;
            }

            previewContent.innerHTML = '<span style="color:var(--text-muted)">Loading preview…</span>';

            fetch('{{ route("admin.emails.preview") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ template_id: templateId, family_id: familyId })
            })
            .then(r => r.json())
            .then(data => {
                previewContent.innerHTML = '<div style="white-space:pre-wrap;font-size:0.82rem;color:var(--text-muted);line-height:1.6">' + data.preview.replace(/</g, '&lt;') + '</div>';
            })
            .catch(() => {
                previewContent.innerHTML = '<span style="color:#f08080">Preview failed.</span>';
            });
        });
    }
})();
</script>
@endsection
