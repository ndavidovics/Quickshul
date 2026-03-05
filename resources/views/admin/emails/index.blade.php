@extends('layouts.admin')
@section('title', 'Email Center')

@section('content')
<div class="flex items-center justify-between" style="margin-bottom:1.5rem">
    <div>
        <h1 class="page-title">Email Center</h1>
        <p class="page-subtitle" style="margin-bottom:0">Send balance reminders and giving statements to members</p>
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

{{-- Tabs --}}
<div style="display:flex;gap:0;margin-bottom:1.25rem;border-bottom:2px solid var(--border-dim)">
    <button class="tab-btn {{ $activeTab === 'balance' ? 'active' : '' }}" data-tab="balance"
        style="padding:0.7rem 1.25rem;border:none;background:none;cursor:pointer;font-size:0.9rem;font-weight:600;margin-bottom:-2px;
               color:{{ $activeTab === 'balance' ? 'var(--gold)' : 'var(--text-muted)' }};
               border-bottom:2px solid {{ $activeTab === 'balance' ? 'var(--gold)' : 'transparent' }}">
        Balance Reminder
    </button>
    <button class="tab-btn {{ $activeTab === 'statement' ? 'active' : '' }}" data-tab="statement"
        style="padding:0.7rem 1.25rem;border:none;background:none;cursor:pointer;font-size:0.9rem;font-weight:600;margin-bottom:-2px;
               color:{{ $activeTab === 'statement' ? 'var(--gold)' : 'var(--text-muted)' }};
               border-bottom:2px solid {{ $activeTab === 'statement' ? 'var(--gold)' : 'transparent' }}">
        Giving Statement
    </button>
</div>

{{-- ====== Tab 1: Balance Reminder ====== --}}
<div id="tab-balance" class="tab-pane" style="{{ $activeTab === 'statement' ? 'display:none' : '' }}">
<div class="grid-2">

<div class="card">
    <div class="card-title">Send Balance Reminder</div>
    <p class="text-sm text-muted" style="margin-bottom:1.25rem;line-height:1.6">
        Sends a personalised email to each family with a secure payment link.
        The link lets them pay outstanding pledges directly — no login required.
        Use <code style="font-size:0.78rem;background:rgba(255,255,255,0.08);padding:0.1rem 0.3rem;border-radius:3px">{greeting}</code>,
        <code style="font-size:0.78rem;background:rgba(255,255,255,0.08);padding:0.1rem 0.3rem;border-radius:3px">{balance}</code>,
        <code style="font-size:0.78rem;background:rgba(255,255,255,0.08);padding:0.1rem 0.3rem;border-radius:3px">{family_name}</code> as merge tags.
    </p>

    <form method="POST" action="{{ route('admin.emails.balance.send') }}" id="balance-form">
        @csrf

        <div class="form-group">
            <label class="form-label">Message <span class="text-muted" style="font-weight:400">(editable)</span></label>
            <textarea name="intro_text" id="balance-intro" class="form-control" rows="6"
                      style="font-size:0.85rem;line-height:1.6;resize:vertical">{{ $defaultBalanceIntro }}</textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Recipients</label>
            <div style="display:flex;flex-direction:column;gap:0.5rem">
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.875rem">
                    <input type="radio" name="recipient_mode" value="all_with_balance" checked>
                    All families with outstanding balance ({{ $familiesWithBalance->count() }})
                </label>
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.875rem">
                    <input type="radio" name="recipient_mode" value="selected">
                    Select specific families
                </label>
            </div>
        </div>

        <div id="balance-family-panel" style="display:none;margin-bottom:1rem">
            <div style="border:1px solid var(--border-dim);border-radius:6px;max-height:220px;overflow-y:auto;padding:0.5rem">
                @foreach($familiesWithBalance as $f)
                <label style="display:flex;align-items:center;gap:0.5rem;padding:0.3rem 0.4rem;cursor:pointer;font-size:0.85rem;border-radius:4px" class="hover-row">
                    <input type="checkbox" name="family_ids[]" value="{{ $f->id }}"
                           class="balance-family-cb"
                           {{ $preselectFamily === $f->id ? 'checked' : '' }}>
                    <span style="flex:1">{{ $f->name }}</span>
                    <span style="color:var(--gold);font-size:0.78rem">${{ number_format($f->outstanding_balance, 2) }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div style="display:flex;gap:0.75rem;flex-wrap:wrap">
            <button type="submit" class="btn btn-gold">Send Balance Reminders</button>
            <button type="button" class="btn btn-outline" id="preview-balance-btn">Preview</button>
            <button type="button" class="btn btn-outline" id="test-balance-btn" style="color:var(--text-muted)">Send Test to Me</button>
        </div>
    </form>
</div>

<div class="card" id="balance-preview-panel">
    <div class="card-title">Email Preview</div>
    <div id="balance-preview-content" style="color:var(--text-muted);font-size:0.85rem;min-height:100px">
        Select a family and click Preview to see the email.
    </div>
</div>

</div>
</div>

{{-- ====== Tab 2: Giving Statement ====== --}}
<div id="tab-statement" class="tab-pane" style="display:none">
<div class="grid-2">

<div class="card">
    <div class="card-title">Send Giving Statement</div>
    <p class="text-sm text-muted" style="margin-bottom:1.25rem;line-height:1.6">
        Sends a personalised payment history statement. Edit the intro text below —
        use <code style="font-size:0.78rem;background:rgba(255,255,255,0.08);padding:0.1rem 0.3rem;border-radius:3px">{greeting}</code>,
        <code style="font-size:0.78rem;background:rgba(255,255,255,0.08);padding:0.1rem 0.3rem;border-radius:3px">{period}</code>,
        <code style="font-size:0.78rem;background:rgba(255,255,255,0.08);padding:0.1rem 0.3rem;border-radius:3px">{total}</code> as merge tags.
    </p>

    <form method="POST" action="{{ route('admin.emails.statement.send') }}" id="statement-form">
        @csrf

        <div style="display:flex;gap:0.75rem;margin-bottom:1rem;flex-wrap:wrap">
            <div class="form-group" style="flex:1;min-width:130px;margin-bottom:0">
                <label class="form-label">From</label>
                <input type="date" name="date_from" id="date-from" class="form-control"
                       value="{{ now()->startOfYear()->format('Y-m-d') }}">
            </div>
            <div class="form-group" style="flex:1;min-width:130px;margin-bottom:0">
                <label class="form-label">To</label>
                <input type="date" name="date_to" id="date-to" class="form-control"
                       value="{{ now()->format('Y-m-d') }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Intro Text <span class="text-muted" style="font-weight:400">(editable)</span></label>
            <textarea name="intro_text" id="intro-text" class="form-control" rows="8"
                      style="font-size:0.85rem;line-height:1.6;resize:vertical">{{ $defaultStatementIntro }}</textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Recipients</label>
            <div style="display:flex;flex-direction:column;gap:0.5rem">
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.875rem">
                    <input type="radio" name="recipient_mode" value="all_families" checked>
                    All families ({{ $allFamilies->count() }})
                </label>
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.875rem">
                    <input type="radio" name="recipient_mode" value="selected">
                    Select specific families
                </label>
            </div>
        </div>

        <div id="statement-family-panel" style="display:none;margin-bottom:1rem">
            <div style="border:1px solid var(--border-dim);border-radius:6px;max-height:200px;overflow-y:auto;padding:0.5rem">
                @foreach($allFamilies as $f)
                <label style="display:flex;align-items:center;gap:0.5rem;padding:0.3rem 0.4rem;cursor:pointer;font-size:0.85rem;border-radius:4px" class="hover-row">
                    <input type="checkbox" name="family_ids[]" value="{{ $f->id }}" class="statement-family-cb">
                    <span style="flex:1">{{ $f->name }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div style="display:flex;gap:0.75rem;flex-wrap:wrap">
            <button type="submit" class="btn btn-gold">Send Statements</button>
            <button type="button" class="btn btn-outline" id="preview-statement-btn">Preview</button>
            <button type="button" class="btn btn-outline" id="test-statement-btn" style="color:var(--text-muted)">Send Test to Me</button>
        </div>
    </form>
</div>

<div class="card" id="statement-preview-panel">
    <div class="card-title">Email Preview</div>
    <div id="statement-preview-content" style="color:var(--text-muted);font-size:0.85rem;min-height:100px">
        Click Preview to render the email.
    </div>
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
                <td class="text-sm text-muted">{{ $send->created_at->format('M j, Y g:i a') }} <span style="font-size:0.7rem;opacity:0.6">CST</span></td>
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
(function () {
    // ----- Tabs -----
    document.querySelectorAll('.tab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.tab-btn').forEach(function (b) {
                b.style.color = 'var(--text-muted)';
                b.style.borderBottomColor = 'transparent';
                b.classList.remove('active');
            });
            document.querySelectorAll('.tab-pane').forEach(function (p) { p.style.display = 'none'; });
            btn.style.color = 'var(--gold)';
            btn.style.borderBottomColor = 'var(--gold)';
            btn.classList.add('active');
            document.getElementById('tab-' + btn.dataset.tab).style.display = '';
        });
    });

    // ----- Balance: recipient toggle -----
    document.querySelectorAll('input[name="recipient_mode"]').forEach(function (r) {
        r.addEventListener('change', function () {
            var panel = r.closest('.tab-pane').querySelector('[id$="-family-panel"]');
            if (panel) panel.style.display = r.value === 'selected' ? 'block' : 'none';
        });
    });

    // ----- Balance: preview -----
    document.getElementById('preview-balance-btn').addEventListener('click', function () {
        var familyId = document.querySelector('.balance-family-cb:checked')?.value
                    || document.querySelector('.balance-family-cb')?.value;
        if (!familyId) {
            document.getElementById('balance-preview-content').innerHTML =
                '<span style="color:#f08080">Select a family first (choose "Specific families" or use the first in the list).</span>';
            return;
        }
        document.getElementById('balance-preview-content').innerHTML = '<span class="text-muted">Loading…</span>';
        fetch('{{ route("admin.emails.balance.preview") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                family_id:  familyId,
                intro_text: document.getElementById('balance-intro').value,
            }),
        }).then(r => r.json()).then(function (data) {
            var iframe = document.createElement('iframe');
            iframe.style.cssText = 'width:100%;border:none;height:500px;border-radius:6px';
            var cont = document.getElementById('balance-preview-content');
            cont.innerHTML = '';
            cont.appendChild(iframe);
            iframe.contentDocument.open();
            iframe.contentDocument.write(data.html);
            iframe.contentDocument.close();
        }).catch(function () {
            document.getElementById('balance-preview-content').innerHTML = '<span style="color:#f08080">Preview failed.</span>';
        });
    });

    // ----- Balance: test email -----
    document.getElementById('test-balance-btn').addEventListener('click', function () {
        var familyId = document.querySelector('.balance-family-cb:checked')?.value
                    || document.querySelector('.balance-family-cb')?.value;
        if (!familyId) { alert('Select a family first.'); return; }
        var btn = this;
        btn.disabled = true;
        btn.textContent = 'Sending…';
        fetch('{{ route("admin.emails.balance.test") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                family_id:  familyId,
                intro_text: document.getElementById('balance-intro').value,
            }),
        }).then(r => r.json()).then(function (data) {
            btn.disabled = false;
            btn.textContent = 'Send Test to Me';
            alert(data.success || data.error || 'Done.');
        }).catch(function () {
            btn.disabled = false;
            btn.textContent = 'Send Test to Me';
            alert('Request failed.');
        });
    });

    // ----- Balance: auto-select pre-selected family -----
    @if($preselectFamily > 0)
    (function () {
        // Switch recipient mode to "selected"
        var radios = document.querySelectorAll('#tab-balance input[name="recipient_mode"]');
        radios.forEach(function (r) { r.checked = r.value === 'selected'; });
        document.getElementById('balance-family-panel').style.display = 'block';

        // Scroll the pre-selected checkbox into view
        var cb = document.querySelector('.balance-family-cb[value="{{ $preselectFamily }}"]');
        if (cb) {
            cb.checked = true;
            cb.closest('label')?.scrollIntoView({ block: 'nearest' });
        }
    })();
    @endif

    // ----- Statement: preview -----
    document.getElementById('preview-statement-btn').addEventListener('click', function () {
        var familyId = document.querySelector('.statement-family-cb:checked')?.value
                    || document.querySelector('.statement-family-cb')?.value;
        if (!familyId) familyId = '{{ $allFamilies->first()?->id }}';
        fetchStatementPreview(familyId);
    });

    function fetchStatementPreview(familyId) {
        document.getElementById('statement-preview-content').innerHTML = '<span class="text-muted">Loading…</span>';
        fetch('{{ route("admin.emails.statement.preview") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                family_id:  familyId,
                date_from:  document.getElementById('date-from').value,
                date_to:    document.getElementById('date-to').value,
                intro_text: document.getElementById('intro-text').value,
            }),
        }).then(r => r.json()).then(function (data) {
            var iframe = document.createElement('iframe');
            iframe.style.cssText = 'width:100%;border:none;height:500px;border-radius:6px';
            var cont = document.getElementById('statement-preview-content');
            cont.innerHTML = '';
            cont.appendChild(iframe);
            iframe.contentDocument.open();
            iframe.contentDocument.write(data.html);
            iframe.contentDocument.close();
        }).catch(function () {
            document.getElementById('statement-preview-content').innerHTML = '<span style="color:#f08080">Preview failed.</span>';
        });
    }

    // ----- Statement: test email -----
    document.getElementById('test-statement-btn').addEventListener('click', function () {
        var btn = this;
        btn.disabled = true;
        btn.textContent = 'Sending…';
        fetch('{{ route("admin.emails.statement.test") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                date_from:  document.getElementById('date-from').value,
                date_to:    document.getElementById('date-to').value,
                intro_text: document.getElementById('intro-text').value,
            }),
        }).then(r => r.json()).then(function (data) {
            btn.disabled = false;
            btn.textContent = 'Send Test to Me';
            if (data.success) {
                alert(data.success || 'Test email sent!');
            } else {
                alert(data.error || 'Failed to send test email.');
            }
        }).catch(function () {
            btn.disabled = false;
            btn.textContent = 'Send Test to Me';
            alert('Request failed.');
        });
    });
})();
</script>
@endsection
