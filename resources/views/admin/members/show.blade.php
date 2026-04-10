@extends('layouts.admin')
@section('title', $family->name)

@section('content')
<div class="flex items-center gap-3" style="margin-bottom:1.5rem;flex-wrap:wrap">
    <a href="{{ route('admin.members') }}" class="btn btn-outline btn-sm">← Members</a>
    <h1 class="page-title" style="margin-bottom:0">{{ $family->name }}</h1>
    <a href="{{ route('admin.members.edit', $family->id) }}" class="btn btn-primary btn-sm">Edit</a>
    @if($family->outstanding_balance > 0)
    <a href="{{ route('admin.members.email', $family->id) }}" class="btn btn-gold btn-sm">✉ Send Balance Email</a>
    @endif
    @if($family->qb_customer_id)
    <form method="POST" action="{{ route('admin.members.push-to-qb', $family->id) }}" style="margin:0">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm" title="Push changes to QuickBooks">↑ Push to QB</button>
    </form>
    @endif
</div>

<div class="grid-2">
    {{-- Family info --}}
    <div class="card">
        <div class="card-title">Family Details</div>
        <table style="width:100%;font-size:0.875rem;border-collapse:collapse">
            @foreach([
                'Membership Type' => $family->membershipType?->label ?? $family->membership_type,
                'Member Since'    => $family->member_since?->format('F j, Y') ?? '—',
                'Phone'           => $family->phone ?? '—',
                'Address'         => trim(($family->address??'').', '.($family->city??'').($family->state?', '.$family->state:'').($family->zip?' '.$family->zip:''), ', '),
            ] as $label => $value)
            <tr>
                <td style="padding:0.5rem 0;color:var(--text-muted);width:40%">{{ $label }}</td>
                <td style="padding:0.5rem 0;font-weight:500">{{ $value }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    {{-- Financial --}}
    <div class="card">
        <div class="card-title">Financials</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div class="stat-card"><div class="stat-label">Pledged</div><div class="stat-value gold">${{ number_format($family->total_pledged,2) }}</div></div>
            <div class="stat-card"><div class="stat-label">Paid</div><div class="stat-value">${{ number_format($family->total_paid,2) }}</div></div>
            <div class="stat-card" style="grid-column:1/-1"><div class="stat-label">Outstanding</div><div class="stat-value {{ $family->outstanding_balance > 0 ? '' : 'gold' }}">${{ number_format($family->outstanding_balance,2) }}</div></div>
        </div>
    </div>
</div>

{{-- Emails --}}
<div class="card" style="margin-top:1.25rem">
    <div class="card-title">Login Emails</div>
    @php $usersByEmail = $family->users->keyBy('email'); @endphp
    @foreach($family->emails as $e)
    @php $u = $usersByEmail[$e->email] ?? null; @endphp
    <div style="padding:0.5rem 0;border-bottom:1px solid var(--border-dim)">
        <div style="display:flex;align-items:center;gap:0.5rem">
            @if($u?->avatar)
                <img src="{{ $u->avatar }}" alt="" style="width:18px;height:18px;border-radius:50%;border:1px solid var(--border-dim)">
            @endif
            <span>{{ $e->email }}</span>
            @if($e->is_primary) <span class="badge badge-gold">Primary</span> @endif
        </div>
        <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.2rem;padding-left:0.1rem">
            @if($u)
                @if($u->last_login_at)
                    Last login: {{ $u->last_login_at->format('M j, Y g:i A') }} &middot; {{ $u->last_login_at->diffForHumans() }}
                @else
                    Never logged in
                @endif
            @else
                No portal account
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- Members --}}
<div class="card" style="margin-top:1.25rem">
    <div class="card-title">Family Members</div>
    <table class="table">
        <thead><tr><th>Name</th><th>Hebrew Name</th><th>Role</th><th>DOB</th><th>Hebrew DOB</th><th></th></tr></thead>
        <tbody>
            @foreach($family->members as $m)
            <tr>
                <td><span style="font-weight:500">{{ $m->full_name }}</span></td>
                <td style="font-size:0.95rem;direction:rtl">{{ $m->hebrew_name ?? '—' }}</td>
                <td><span class="badge badge-muted">{{ ucfirst($m->role) }}</span></td>
                <td class="text-muted text-sm">{{ $m->date_of_birth?->format('M j, Y') ?? '—' }}</td>
                <td class="text-sm">{{ $m->hebrew_date_of_birth ?? '—' }}</td>
                <td><a href="{{ route('admin.members.edit-member', [$family->id, $m->id]) }}" class="btn btn-outline btn-sm">Edit</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Yahrtzeits --}}
@if($family->yahrtzeits->isNotEmpty())
<div class="card" style="margin-top:1.25rem">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem">
        <div class="card-title" style="margin-bottom:0">Yahrtzeits</div>
        <a href="{{ route('admin.members.edit', $family->id) }}#yahrtzeits" class="btn btn-outline btn-sm">Manage</a>
    </div>
    <table class="table">
        <thead><tr><th>Name</th><th>Hebrew Name</th><th>Relationship</th><th>Date of Death</th><th>Annual Yahrzeit</th></tr></thead>
        <tbody>
            @foreach($family->yahrtzeits as $y)
            @php
                $months = [1=>'Tishrei',2=>'Cheshvan',3=>'Kislev',4=>'Tevet',5=>'Shevat',
                           6=>'Adar I',7=>'Adar/Adar II',8=>'Nisan',9=>'Iyar',10=>'Sivan',
                           11=>'Tammuz',12=>'Av',13=>'Elul'];
            @endphp
            <tr>
                <td style="font-weight:500">{{ $y->full_name }}</td>
                <td style="direction:rtl;font-family:serif;font-size:0.95rem">{{ $y->hebrew_name ?? '—' }}</td>
                <td>{{ $y->relationship_label ?? '—' }}</td>
                <td class="text-muted text-sm">{{ $y->date_of_death?->format('M j, Y') ?? '—' }}</td>
                <td class="text-sm">
                    {{ $y->hebrew_day }} {{ $months[$y->hebrew_month] ?? '' }}
                    @if($y->hebrew_date_of_death)
                        <span class="text-muted" style="font-size:0.75rem;margin-left:0.4rem">{{ $y->hebrew_date_of_death }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Pledges --}}
<div class="card" style="margin-top:1.25rem">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem">
        <div class="card-title" style="margin-bottom:0">Pledge / Invoice History</div>
        <a href="{{ route('admin.members.export.pledges', $family->id) }}" class="btn btn-outline btn-sm" title="Export CSV">⬇ CSV</a>
    </div>
    <table class="table">
        <thead><tr><th>Date</th><th>Description</th><th>Amount</th><th>Balance Due</th><th>Status</th></tr></thead>
        <tbody id="pledges-tbody">
            @include('admin.members._pledges')
        </tbody>
    </table>
</div>

{{-- Payments --}}
<div class="card" style="margin-top:1.25rem">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem">
        <div class="card-title" style="margin-bottom:0">Payment History</div>
        <a href="{{ route('admin.members.export.payments', $family->id) }}" class="btn btn-outline btn-sm" title="Export CSV">⬇ CSV</a>
    </div>
    <table class="table">
        <thead><tr><th>Date</th><th>Description</th><th>Amount</th><th>Reference</th></tr></thead>
        <tbody id="payments-tbody">
            @include('admin.members._payments')
        </tbody>
    </table>
</div>

@section('scripts')
<script>
(function () {
    function ajaxPaginate(tbodyId, endpoint) {
        var tbody = document.getElementById(tbodyId);
        if (!tbody) return;
        tbody.addEventListener('click', function (e) {
            var link = e.target.closest('a[href]');
            if (!link) return;
            e.preventDefault();
            var url = new URL(link.href);
            var page = url.searchParams.get('page') || '1';
            url.searchParams.forEach(function (v) { if (/^\d+$/.test(v)) page = v; });
            tbody.style.opacity = '0.5';
            fetch(endpoint + '?page=' + page, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (d) { tbody.innerHTML = d.html; tbody.style.opacity = ''; })
                .catch(function () { tbody.style.opacity = ''; });
        });
    }
    ajaxPaginate('pledges-tbody',  '{{ route('admin.members.pledges.ajax', $family->id) }}');
    ajaxPaginate('payments-tbody', '{{ route('admin.members.payments.ajax', $family->id) }}');
})();
</script>
@endsection

{{-- Audit log --}}
@if($auditLogs->count())
<div class="card" style="margin-top:1.25rem">
    <div class="card-title">Audit Log</div>
    <table class="table">
        <thead><tr><th>When</th><th>Action</th><th>By</th><th>Description</th></tr></thead>
        <tbody>
            @foreach($auditLogs as $log)
            <tr>
                <td class="text-muted text-sm">{{ $log->created_at->format('M j, Y g:i a') }}</td>
                <td><span class="badge badge-muted">{{ $log->action }}</span></td>
                <td class="text-muted text-sm">{{ $log->user?->name ?? 'System' }}</td>
                <td class="text-sm">{{ $log->description }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
