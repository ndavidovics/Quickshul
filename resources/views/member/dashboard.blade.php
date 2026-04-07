@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="flex items-center justify-between" style="margin-bottom:1.75rem">
    <div>
        <h1 class="page-title">Shalom, {{ auth()->user()->name }}</h1>
        <p class="page-subtitle" style="margin-bottom:0">
            @if($family) {{ $family->name }} Family &mdash; {{ $family->membership_type->label() }}
            @else No family account linked yet. Contact the office.
            @endif
        </p>
    </div>
    @if($family)
    <div style="display:flex;gap:0.6rem;flex-wrap:wrap">
        <a href="{{ route('donate') }}" class="btn btn-gold">Donate</a>
        <a href="{{ route('family') }}" class="btn btn-outline">Edit My Info</a>
    </div>
    @endif
</div>

@if($family)
{{-- Stats row --}}
<div class="grid-2" style="margin-bottom:1.5rem">
    <div class="stat-card">
        <div class="stat-label">Outstanding Balance</div>
        <div class="stat-value {{ $family->outstanding_balance > 0 ? '' : 'gold' }}">
            ${{ number_format($family->outstanding_balance, 2) }}
        </div>
        <div class="stat-sub">Member since {{ $family->member_since?->format('Y') ?? '—' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Donated (Past 12 Months)</div>
        <div class="stat-value gold">${{ number_format($paidPast12Months, 2) }}</div>
        <div class="stat-sub">{{ $family->members->count() }} family member{{ $family->members->count() === 1 ? '' : 's' }}</div>
    </div>
</div>

<div class="grid-2">
    {{-- Upcoming yahrtzeits --}}
    <div class="card">
        <div class="card-title">⭐ Upcoming Yahrtzeits <span style="font-size:0.75rem;font-weight:400;color:var(--text-muted)">(next 60 days)</span></div>
        @if($yahrtzeits->isEmpty())
            <p class="text-muted text-sm">No yahrtzeits in the next 60 days.</p>
        @else
            @foreach($yahrtzeits as $y)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;border-bottom:1px solid var(--border-dim)">
                <div>
                    <div style="font-weight:500">{{ $y['yahrtzeit']->full_name }}</div>
                    @if($y['yahrtzeit']->relationship_label)
                        <div class="text-sm text-muted">{{ $y['yahrtzeit']->relationship_label }}</div>
                    @endif
                    <div class="text-sm text-muted">{{ $y['hebrew_date']['day'] }} {{ $y['hebrew_date']['month_name'] }}</div>
                </div>
                <div style="text-align:right">
                    <div class="badge badge-gold">{{ $y['gregorian_date']->format('M j') }}</div>
                    <div class="text-sm text-muted" style="margin-top:0.2rem">{{ $y['gregorian_date']->diffForHumans() }}</div>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    {{-- Upcoming birthdays --}}
    <div class="card">
        <div class="card-title">🎂 Upcoming Hebrew Birthdays <span style="font-size:0.75rem;font-weight:400;color:var(--text-muted)">(next 60 days)</span></div>
        @if($birthdays->isEmpty())
            <p class="text-muted text-sm">No birthdays in the next 60 days.</p>
        @else
            @foreach($birthdays as $b)
            <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:0.75rem 0;border-bottom:1px solid var(--border-dim)">
                <div style="flex:1">
                    <div style="font-weight:500;margin-bottom:0.25rem">{{ $b['member']->full_name }}</div>
                    <div class="text-sm text-muted">Hebrew birthday: {{ $b['hebrew_date']['day'] }} {{ $b['hebrew_date']['month_name'] }}</div>
                    <div class="text-sm text-muted">observed on {{ $b['gregorian_date']->format('M j, Y') }}</div>
                </div>
                <div style="text-align:right;margin-left:1rem">
                    <!-- <div class="text-sm" style="margin-bottom:0.25rem">English Birthday</div> -->
                    <div class="badge badge-blue" style="display:inline-block">{{ $b['actual_gregorian_date']->format('M j') }}</div>
                    <!-- <div class="text-sm text-muted" style="margin-top:0.3rem">{{ $b['actual_gregorian_date']->diffForHumans() }}</div> -->
                </div>
            </div>
            @endforeach
        @endif
    </div>
</div>

{{-- Balance alert + quick pay --}}


{{-- Open pledges --}}
@if($openPledges->count())
<div class="card" style="margin-top:1.25rem;border-color:rgba(201,168,76,0.3)">
    <div class="card-title">Open Pledges</div>
    <table class="table">
        <thead><tr><th>Date</th><th>Description</th><th>Pledged</th><th>Balance Due</th><th></th></tr></thead>
        <tbody>
            @foreach($openPledges as $pledge)
            <tr>
                <td class="text-sm text-muted">{{ $pledge->invoice_date->format('M j, Y') }}</td>
                <td class="text-sm">{{ $pledge->description ?: '—' }}</td>
                <td style="font-weight:600;color:var(--gold)">${{ number_format($pledge->amount, 2) }}</td>
                <td style="color:var(--gold)">${{ number_format($pledge->balance, 2) }}</td>
                <td>
                    <a href="{{ route('donate', [
                            'pledge_id'   => $pledge->id,
                            'amount'      => number_format($pledge->balance, 2, '.', ''),
                            'description' => $pledge->description,
                        ]) }}"
                       class="btn btn-gold btn-sm">Pay</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Recent payments --}}
@if($recentPayments->count())
<div class="card" style="margin-top:1.25rem">
    <div class="card-title">Recent Payments</div>
    <table class="table">
        <thead><tr><th>Date</th><th>Description</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($recentPayments as $p)
            <tr>
                <td>{{ $p->payment_date->format('M j, Y') }}</td>
                <td class="text-sm text-muted">{{ $p->description ?: '—' }}</td>
                <td style="font-weight:600;color:var(--gold)">${{ number_format($p->amount, 2) }}</td>
                <td><span class="badge badge-green">{{ $p->status->label() }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top:0.75rem"><a href="{{ route('financial') }}" class="btn btn-outline btn-sm">View All →</a></div>
</div>
@endif

@else
{{-- No family linked --}}
<div class="card" style="text-align:center;padding:3rem">
    <div style="font-size:2.5rem;margin-bottom:1rem">🕍</div>
    <h2 style="font-family:'Playfair Display',serif;color:var(--gold);margin-bottom:0.5rem">Welcome to the {{ $tenant->name ?? config('app.name') }} Member Portal</h2>
    <p class="text-muted" style="max-width:400px;margin:0 auto">Your account has not yet been linked to a family record. Please contact the synagogue office at <a href="mailto:{{ $tenant->org_email ?? '' }}" style="color:var(--gold)">{{ $tenant->org_email ?? '' }}</a> to complete your setup.</p>
</div>
@endif
@endsection
