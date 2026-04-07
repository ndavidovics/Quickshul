@extends('superadmin.layout')
@section('title', $tenant->name)

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <a href="{{ route('superadmin.index') }}" style="font-size:0.8rem;color:var(--text-muted);text-decoration:none">← Back to Tenants</a>
        <div class="page-title" style="margin-top:0.4rem">{{ $tenant->name }}</div>
        <div class="page-subtitle">
            <a href="https://{{ $tenant->slug }}.quickshul.com" target="_blank" style="color:var(--gold)">
                {{ $tenant->slug }}.quickshul.com ↗
            </a>
            &nbsp;&mdash;&nbsp;
            @if($tenant->status === 'active')
                <span class="badge badge-green">Active</span>
            @elseif($tenant->status === 'pending')
                <span class="badge badge-yellow">Pending</span>
            @elseif($tenant->status === 'suspended')
                <span class="badge badge-red">Suspended</span>
            @else
                <span class="badge badge-muted">{{ $tenant->status }}</span>
            @endif
            @if($tenant->deleted_at)
                <span class="badge badge-red" style="margin-left:0.5rem">Deleted</span>
            @endif
        </div>
    </div>

    <div class="flex gap-2">
        @if($tenant->status !== 'active' && !$tenant->deleted_at)
        <form method="POST" action="{{ route('superadmin.tenants.activate', $tenant->id) }}">
            @csrf
            <button type="submit" class="btn btn-gold">Activate</button>
        </form>
        @endif
        @if($tenant->status === 'active' && !$tenant->deleted_at)
        <form method="POST" action="{{ route('superadmin.tenants.suspend', $tenant->id) }}">
            @csrf
            <button type="submit" class="btn btn-warning">Suspend</button>
        </form>
        @endif
        @if(!$tenant->deleted_at)
        <form method="POST" action="{{ route('superadmin.tenants.destroy', $tenant->id) }}"
              onsubmit="return confirm('Permanently delete this tenant? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
        @endif
    </div>
</div>

{{-- Stats --}}
<div class="grid-3">
    <div class="stat-card">
        <div class="stat-label">Families</div>
        <div class="stat-value">{{ number_format($stats['families']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Users</div>
        <div class="stat-value">{{ number_format($stats['users']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Payments</div>
        <div class="stat-value gold">{{ number_format($stats['payments']) }}</div>
    </div>
</div>

{{-- Organization Info --}}
<div class="card">
    <div class="card-title">Organization Info</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div>
            <div class="text-muted text-sm" style="margin-bottom:0.2rem">Name</div>
            <div>{{ $tenant->name }}</div>
        </div>
        <div>
            <div class="text-muted text-sm" style="margin-bottom:0.2rem">Slug</div>
            <div>{{ $tenant->slug }}</div>
        </div>
        @if($tenant->tagline)
        <div>
            <div class="text-muted text-sm" style="margin-bottom:0.2rem">Tagline</div>
            <div>{{ $tenant->tagline }}</div>
        </div>
        @endif
        @if($tenant->org_email)
        <div>
            <div class="text-muted text-sm" style="margin-bottom:0.2rem">Email</div>
            <div><a href="mailto:{{ $tenant->org_email }}" style="color:var(--gold)">{{ $tenant->org_email }}</a></div>
        </div>
        @endif
        @if($tenant->org_phone)
        <div>
            <div class="text-muted text-sm" style="margin-bottom:0.2rem">Phone</div>
            <div>{{ $tenant->org_phone }}</div>
        </div>
        @endif
        @if($tenant->org_address)
        <div style="grid-column:1/-1">
            <div class="text-muted text-sm" style="margin-bottom:0.2rem">Address</div>
            <div>{{ $tenant->org_address }}@if($tenant->org_city), {{ $tenant->org_city }}@endif @if($tenant->org_state) {{ $tenant->org_state }}@endif @if($tenant->org_zip) {{ $tenant->org_zip }}@endif</div>
        </div>
        @endif
        <div>
            <div class="text-muted text-sm" style="margin-bottom:0.2rem">Timezone</div>
            <div>{{ $tenant->timezone ?? '—' }}</div>
        </div>
        <div>
            <div class="text-muted text-sm" style="margin-bottom:0.2rem">Registered</div>
            <div>{{ $tenant->created_at->format('M j, Y g:i A') }}</div>
        </div>
        @if($tenant->deleted_at)
        <div>
            <div class="text-muted text-sm" style="margin-bottom:0.2rem">Deleted</div>
            <div style="color:#f08080">{{ $tenant->deleted_at->format('M j, Y g:i A') }}</div>
        </div>
        @endif
    </div>
</div>

{{-- Integrations --}}
<div class="card">
    <div class="card-title">Integration Status</div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem">
        <div style="background:var(--bg-card2);border:1px solid var(--border-dim);border-radius:10px;padding:1rem;text-align:center">
            <div style="font-size:1.75rem;margin-bottom:0.5rem">{{ $tenant->isGmailConnected() ? '✅' : '❌' }}</div>
            <div style="font-weight:600;color:#fff;margin-bottom:0.25rem">Gmail</div>
            @if($tenant->isGmailConnected())
                <div class="text-sm text-muted">{{ $tenant->gmail_email ?? 'Connected' }}</div>
            @else
                <div class="text-sm text-muted">Not connected</div>
            @endif
        </div>
        <div style="background:var(--bg-card2);border:1px solid var(--border-dim);border-radius:10px;padding:1rem;text-align:center">
            <div style="font-size:1.75rem;margin-bottom:0.5rem">{{ $tenant->isPayPalConnected() ? '✅' : '❌' }}</div>
            <div style="font-weight:600;color:#fff;margin-bottom:0.25rem">PayPal</div>
            @if($tenant->isPayPalConnected())
                <div class="text-sm text-muted">{{ $tenant->paypal_mode ?? 'sandbox' }} mode</div>
            @else
                <div class="text-sm text-muted">Not connected</div>
            @endif
        </div>
        <div style="background:var(--bg-card2);border:1px solid var(--border-dim);border-radius:10px;padding:1rem;text-align:center">
            <div style="font-size:1.75rem;margin-bottom:0.5rem">{{ $tenant->qb_enabled ? '✅' : '❌' }}</div>
            <div style="font-weight:600;color:#fff;margin-bottom:0.25rem">QuickBooks</div>
            <div class="text-sm text-muted">{{ $tenant->qb_enabled ? 'Enabled' : 'Disabled' }}</div>
        </div>
    </div>
</div>

{{-- Onboarding --}}
<div class="card">
    <div class="card-title">Onboarding</div>
    <div>
        <div class="text-muted text-sm" style="margin-bottom:0.2rem">Onboarding Step</div>
        <div>{{ $tenant->onboarding_step ?? '—' }}</div>
    </div>
</div>
@endsection
