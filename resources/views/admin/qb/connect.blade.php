@extends('layouts.admin')
@section('title', 'QuickBooks Connection')

@section('content')
<div class="flex items-center gap-3" style="margin-bottom:1.5rem">
    <a href="{{ route('admin.qb') }}" class="btn btn-outline btn-sm">← QB Dashboard</a>
    <h1 class="page-title" style="margin-bottom:0">QuickBooks Connection</h1>
</div>

@if($errors->any())
<div style="background:rgba(240,128,128,0.1);border:1px solid rgba(240,128,128,0.4);border-radius:6px;padding:0.875rem 1rem;margin-bottom:1.25rem;color:#f08080;font-size:0.875rem">
    @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
</div>
@endif

<div style="max-width:600px">
    @if($isConnected)
    <div class="card" style="border-color:rgba(100,200,120,0.4)">
        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem">
            <div style="width:48px;height:48px;border-radius:50%;background:rgba(100,200,120,0.15);display:flex;align-items:center;justify-content:center;font-size:1.5rem">✓</div>
            <div>
                <div style="font-weight:600;color:#7ecf8e">QuickBooks Connected</div>
                <div class="text-sm text-muted">Your portal is linked to QuickBooks Online</div>
            </div>
        </div>
        <p class="text-sm text-muted" style="margin-bottom:1.25rem">
            The connection is active and syncing. You can reconnect if you encounter authentication errors, or disconnect to unlink QuickBooks from the portal.
        </p>
        <div style="display:flex;gap:0.75rem">
            <a href="{{ route('admin.qb.redirect') }}" class="btn btn-outline btn-sm">Reconnect</a>
            <form method="POST" action="{{ route('admin.qb.disconnect') }}" onsubmit="return confirm('Disconnect QuickBooks? Automatic sync will stop.')">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">Disconnect</button>
            </form>
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-title">Connect QuickBooks Online</div>
        <p class="text-muted text-sm" style="margin-bottom:1.25rem">
            Linking QuickBooks Online enables automatic synchronization of member accounts and payments. Member pledges and payment records will stay in sync between the portal and your QB company.
        </p>

        <div style="background:rgba(201,168,76,0.08);border:1px solid rgba(201,168,76,0.25);border-radius:6px;padding:1rem;margin-bottom:1.25rem">
            <div style="font-size:0.8rem;font-weight:600;color:var(--gold);margin-bottom:0.5rem">What will be synced</div>
            <ul style="margin:0;padding-left:1.25rem;font-size:0.85rem;color:var(--text-muted);line-height:1.8">
                <li>Customer records → Family accounts (name, address, phone)</li>
                <li>Payments → Payment history (amount, date, transaction ID)</li>
                <li>Custom fields → Membership type</li>
                <li>Automatic daily sync at 2:00 AM</li>
            </ul>
        </div>

        <a href="{{ route('admin.qb.redirect') }}" class="btn btn-gold" style="display:inline-flex;align-items:center;gap:0.6rem">
            <svg width="20" height="20" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="20" cy="20" r="20" fill="#2CA01C"/>
                <text x="20" y="26" text-anchor="middle" fill="white" font-size="16" font-weight="bold">QB</text>
            </svg>
            Connect with QuickBooks
        </a>
    </div>

    <div class="card" style="margin-top:1.25rem">
        <div class="card-title" style="font-size:0.85rem">OAuth Configuration</div>
        <table style="width:100%;font-size:0.8rem;border-collapse:collapse">
            <tr>
                <td style="padding:0.4rem 0;color:var(--text-muted);width:40%">Environment</td>
                <td style="padding:0.4rem 0;font-family:monospace">{{ config('services.quickbooks.environment', 'production') }}</td>
            </tr>
            <tr>
                <td style="padding:0.4rem 0;color:var(--text-muted)">Redirect URI</td>
                <td style="padding:0.4rem 0;font-family:monospace;font-size:0.72rem;word-break:break-all">{{ config('services.quickbooks.redirect_uri') }}</td>
            </tr>
            <tr>
                <td style="padding:0.4rem 0;color:var(--text-muted)">Client ID</td>
                <td style="padding:0.4rem 0;font-family:monospace;font-size:0.72rem">{{ substr(config('services.quickbooks.client_id', ''), 0, 12) }}…</td>
            </tr>
        </table>
    </div>
    @endif
</div>
@endsection
