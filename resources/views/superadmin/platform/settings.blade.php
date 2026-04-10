@extends('superadmin.layout')
@section('title', 'Platform Settings')

@section('content')
<div style="margin-bottom:1.75rem">
    <div class="page-title">Platform Settings</div>
    <div class="page-subtitle">Platform-level configuration — applies to all tenants</div>
</div>

{{-- Platform Gmail --}}
<div class="card">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.25rem">
        <div style="display:flex;align-items:center;gap:0.6rem">
            <span style="font-size:1.1rem">✉️</span>
            <div>
                <div style="font-weight:700;font-size:1rem;color:#fff">Platform Gmail</div>
                <div class="text-sm text-muted">Used to send "Find Your Portal" emails and other platform notifications</div>
            </div>
        </div>
        @if($gmailConnected)
            <span style="background:rgba(46,204,113,0.12);color:#2ecc71;border:1px solid rgba(46,204,113,0.3);border-radius:20px;padding:0.2rem 0.7rem;font-size:0.75rem;font-weight:600">● Connected</span>
        @else
            <span style="background:rgba(231,76,60,0.1);color:#e74c3c;border:1px solid rgba(231,76,60,0.25);border-radius:20px;padding:0.2rem 0.7rem;font-size:0.75rem;font-weight:600">○ Not connected</span>
        @endif
    </div>

    @if($gmailConnected)
        <div style="background:rgba(255,255,255,0.04);border:1px solid var(--border-dim);border-radius:8px;padding:0.875rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:0.75rem">
            <span style="font-size:1.1rem">📧</span>
            <div>
                <div style="font-weight:500;font-size:0.9rem;color:#fff">{{ $gmailEmail }}</div>
                <div class="text-sm text-muted">Platform emails sent from this address</div>
            </div>
        </div>

        {{-- Test email form --}}
        <form method="POST" action="{{ route('superadmin.platform.gmail.test') }}" style="display:flex;gap:0.75rem;margin-bottom:1rem;flex-wrap:wrap">
            @csrf
            <input type="email" name="to" placeholder="Send test to..." required
                   value="{{ auth()->user()->email }}"
                   style="flex:1;min-width:200px;padding:0.5rem 0.75rem;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:7px;font-size:0.85rem;font-family:'Inter',sans-serif;color:var(--text);outline:none">
            <button type="submit" class="btn btn-outline btn-sm">Send Test Email</button>
        </form>

        <div style="display:flex;gap:0.75rem">
            <a href="{{ route('superadmin.platform.gmail.connect') }}" class="btn btn-outline btn-sm">Reconnect</a>
            <form method="POST" action="{{ route('superadmin.platform.gmail.disconnect') }}"
                  onsubmit="return confirm('Disconnect platform Gmail? Find-your-portal emails will stop working.')">
                @csrf
                <button type="submit" class="btn btn-sm" style="background:rgba(231,76,60,0.12);color:#e74c3c;border:1px solid rgba(231,76,60,0.3)">Disconnect</button>
            </form>
        </div>
    @else
        <p class="text-sm text-muted" style="margin-bottom:1.25rem">
            Connect a Gmail or Google Workspace account so QuickShul can send platform-level emails
            (portal finder links, system notifications). This is separate from per-shul Gmail connections.
        </p>
        <a href="{{ route('superadmin.platform.gmail.connect') }}" class="btn btn-gold">Connect Gmail Account</a>
    @endif
</div>

{{-- Future settings placeholder --}}
<div class="card" style="opacity:0.5">
    <div style="font-weight:600;font-size:0.9rem;color:var(--text-muted);margin-bottom:0.4rem">More platform settings coming soon</div>
    <div class="text-sm text-muted">Global rate limits, default membership types, announcement banners, billing configuration.</div>
</div>

@endsection
