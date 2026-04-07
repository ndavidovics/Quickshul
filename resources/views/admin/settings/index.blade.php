@extends('layouts.admin')
@section('title', 'Settings')

@section('content')
<div class="page-title" style="margin-bottom:0.25rem">Settings</div>
<div class="page-subtitle" style="margin-bottom:2rem">Organization profile and integration settings</div>

{{-- ── Organization Profile ─────────────────────────────────── --}}
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-header" style="display:flex;align-items:center;gap:0.6rem;margin-bottom:1.5rem">
        <span style="font-size:1.1rem">🏛️</span>
        <div>
            <div style="font-weight:700;font-size:1rem;color:var(--text)">Organization Profile</div>
            <div class="text-sm text-muted">Name, contact info, and branding</div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.profile') }}">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem 1.5rem">

            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Organization Name <span style="color:#e74c3c">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $tenant->name) }}" required>
            </div>

            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Tagline <span class="text-muted text-sm">(optional)</span></label>
                <input type="text" name="tagline" class="form-control" placeholder="e.g. Torah · Tefillah · Tradition" value="{{ old('tagline', $tenant->tagline) }}">
            </div>

            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Contact Email</label>
                <input type="email" name="org_email" class="form-control" placeholder="office@yourshul.org" value="{{ old('org_email', $tenant->org_email) }}">
            </div>

            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Phone</label>
                <input type="text" name="org_phone" class="form-control" placeholder="(555) 123-4567" value="{{ old('org_phone', $tenant->org_phone) }}">
            </div>

            <div class="form-group" style="margin-bottom:0;grid-column:1/-1">
                <label class="form-label">Address</label>
                <input type="text" name="org_address" class="form-control" placeholder="123 Main St, City, State ZIP" value="{{ old('org_address', $tenant->org_address) }}">
            </div>

            <div class="form-group" style="margin-bottom:0;grid-column:1/-1">
                <label class="form-label">Logo URL <span class="text-muted text-sm">(optional)</span></label>
                <input type="url" name="logo_url" class="form-control" placeholder="https://..." value="{{ old('logo_url', $tenant->logo_url) }}">
                @if($tenant->logo_url)
                    <div style="margin-top:0.5rem"><img src="{{ $tenant->logo_url }}" alt="Logo" style="height:40px;border-radius:4px;opacity:0.85"></div>
                @endif
            </div>

            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Timezone</label>
                <select name="timezone" class="form-control">
                    @foreach(\DateTimeZone::listIdentifiers(\DateTimeZone::ALL) as $tz)
                        <option value="{{ $tz }}" {{ ($tenant->timezone ?? 'America/Chicago') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Portal URL</label>
                <input type="text" class="form-control" value="{{ $tenant->slug }}.{{ config('app.root_domain') }}" readonly style="opacity:0.6;cursor:not-allowed">
                <div class="text-sm text-muted" style="margin-top:0.3rem">Subdomain cannot be changed after registration</div>
            </div>

        </div>

        <div style="margin-top:1.5rem">
            <button type="submit" class="btn btn-gold">Save Profile</button>
        </div>
    </form>
</div>

{{-- ── Gmail ─────────────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:1.5rem">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:0.6rem">
            <span style="font-size:1.1rem">✉️</span>
            <div>
                <div style="font-weight:700;font-size:1rem;color:var(--text)">Gmail / Google Workspace</div>
                <div class="text-sm text-muted">Used to send balance reminders and giving statements</div>
            </div>
        </div>
        @if($tenant->gmail_email)
            <span style="background:rgba(46,204,113,0.12);color:#2ecc71;border:1px solid rgba(46,204,113,0.3);border-radius:20px;padding:0.2rem 0.7rem;font-size:0.75rem;font-weight:600">● Connected</span>
        @else
            <span style="background:rgba(231,76,60,0.1);color:#e74c3c;border:1px solid rgba(231,76,60,0.25);border-radius:20px;padding:0.2rem 0.7rem;font-size:0.75rem;font-weight:600">○ Not connected</span>
        @endif
    </div>

    <div style="margin-top:1.25rem">
        @if($tenant->gmail_email)
            <div style="background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:6px;padding:0.875rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:0.75rem">
                <span style="font-size:1.1rem">📧</span>
                <div>
                    <div style="font-weight:500;font-size:0.9rem;color:var(--text)">{{ $tenant->gmail_email }}</div>
                    <div class="text-sm text-muted">Emails are sent from this address</div>
                </div>
            </div>
            <div style="display:flex;gap:0.75rem">
                <a href="{{ route('admin.settings.gmail.connect') }}" class="btn btn-outline btn-sm">Reconnect</a>
                <form method="POST" action="{{ route('admin.settings.gmail.disconnect') }}" style="display:inline"
                      onsubmit="return confirm('Disconnect Gmail? Emails will fail until reconnected.')">
                    @csrf
                    <button type="submit" class="btn btn-sm" style="background:rgba(231,76,60,0.12);color:#e74c3c;border:1px solid rgba(231,76,60,0.3)">Disconnect</button>
                </form>
            </div>
        @else
            <p class="text-sm text-muted" style="margin-bottom:1rem">Connect a Gmail or Google Workspace account so the portal can send emails on behalf of your organization.</p>
            @if(!config('services.google.client_id'))
                <div style="background:rgba(201,168,76,0.08);border:1px solid rgba(201,168,76,0.3);border-radius:6px;padding:0.875rem 1rem;font-size:0.85rem;color:var(--gold)">
                    ⚠️ Google OAuth is not configured on this platform yet. Contact the QuickShul administrator.
                </div>
            @else
                <a href="{{ route('admin.settings.gmail.connect') }}" class="btn btn-gold btn-sm">Connect Gmail Account</a>
            @endif
        @endif
    </div>
</div>

{{-- ── PayPal ────────────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:1.5rem">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.25rem">
        <div style="display:flex;align-items:center;gap:0.6rem">
            <span style="font-size:1.1rem">💳</span>
            <div>
                <div style="font-weight:700;font-size:1rem;color:var(--text)">PayPal</div>
                <div class="text-sm text-muted">Accept pledge payments and donations online</div>
            </div>
        </div>
        @if($tenant->paypal_client_id)
            <span style="background:rgba(46,204,113,0.12);color:#2ecc71;border:1px solid rgba(46,204,113,0.3);border-radius:20px;padding:0.2rem 0.7rem;font-size:0.75rem;font-weight:600">● Connected · {{ ucfirst($tenant->paypal_mode ?? 'live') }}</span>
        @else
            <span style="background:rgba(231,76,60,0.1);color:#e74c3c;border:1px solid rgba(231,76,60,0.25);border-radius:20px;padding:0.2rem 0.7rem;font-size:0.75rem;font-weight:600">○ Not connected</span>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.settings.paypal') }}">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem 1.5rem">

            <div class="form-group" style="margin-bottom:0;grid-column:1/-1">
                <label class="form-label">Mode</label>
                <div style="display:flex;gap:1rem">
                    <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;font-size:0.9rem">
                        <input type="radio" name="paypal_mode" value="live" {{ ($tenant->paypal_mode ?? 'live') === 'live' ? 'checked' : '' }}>
                        <span>Live (production)</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;font-size:0.9rem">
                        <input type="radio" name="paypal_mode" value="sandbox" {{ ($tenant->paypal_mode ?? '') === 'sandbox' ? 'checked' : '' }}>
                        <span>Sandbox (testing)</span>
                    </label>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Client ID</label>
                <input type="text" name="paypal_client_id" class="form-control" placeholder="A...xyz"
                       value="{{ old('paypal_client_id', $tenant->paypal_client_id) }}">
            </div>

            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Secret</label>
                <input type="password" name="paypal_secret" class="form-control" placeholder="{{ $tenant->paypal_secret ? '••••••••' : 'Enter secret' }}">
                @if($tenant->paypal_secret)
                    <div class="text-sm text-muted" style="margin-top:0.3rem">Leave blank to keep existing secret</div>
                @endif
            </div>

        </div>

        <div style="margin-top:1.25rem">
            <button type="submit" class="btn btn-gold">Save &amp; Verify PayPal</button>
        </div>
    </form>
</div>

{{-- ── QuickBooks ────────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:1.5rem">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:0.6rem">
            <span style="font-size:1.1rem">📊</span>
            <div>
                <div style="font-weight:700;font-size:1rem;color:var(--text)">QuickBooks Online</div>
                <div class="text-sm text-muted">Optional — sync member balances and payments with QuickBooks</div>
            </div>
        </div>
        @if($tenant->qb_enabled)
            <span style="background:rgba(46,204,113,0.12);color:#2ecc71;border:1px solid rgba(46,204,113,0.3);border-radius:20px;padding:0.2rem 0.7rem;font-size:0.75rem;font-weight:600">● Enabled</span>
        @else
            <span style="background:rgba(136,153,187,0.15);color:var(--text-muted);border:1px solid rgba(136,153,187,0.2);border-radius:20px;padding:0.2rem 0.7rem;font-size:0.75rem;font-weight:600">○ Disabled</span>
        @endif
    </div>

    <p class="text-sm text-muted" style="margin:1rem 0">
        When enabled, the QuickBooks Sync section appears in your admin menu. You can then connect your QuickBooks Online account and sync member balances.
    </p>

    <div style="display:flex;align-items:center;gap:1rem">
        <form method="POST" action="{{ route('admin.settings.qb.toggle') }}">
            @csrf
            @if($tenant->qb_enabled)
                <button type="submit" class="btn btn-outline btn-sm"
                        onclick="return confirm('Disable QuickBooks? The sync menu will be hidden.')">
                    Disable QuickBooks
                </button>
            @else
                <button type="submit" class="btn btn-gold btn-sm">Enable QuickBooks</button>
            @endif
        </form>

        @if($tenant->qb_enabled)
            <a href="{{ route('admin.qb') }}" class="btn btn-outline btn-sm">→ Go to QB Sync</a>
        @endif
    </div>
</div>

@endsection
