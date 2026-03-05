@extends('layouts.admin')
@section('title', 'QuickBooks Sync')

@section('content')
<div class="flex items-center justify-between" style="margin-bottom:1.5rem">
    <div>
        <h1 class="page-title">QuickBooks Sync</h1>
        <p class="page-subtitle" style="margin-bottom:0">Manage QuickBooks Online connection and data sync</p>
    </div>
    <a href="{{ route('admin.qb.connect') }}" class="btn btn-outline btn-sm">Connection Settings</a>
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

{{-- Status cards --}}
<div class="grid-3" style="margin-bottom:1.5rem">
    <div class="stat-card">
        <div class="stat-label">Connection Status</div>
        <div style="margin-top:0.5rem">
            @if($isConnected)
                <span class="badge badge-green" style="font-size:0.85rem;padding:0.3rem 0.7rem">● Connected</span>
                @if($connection)
                <div class="text-sm text-muted" style="margin-top:0.4rem">Realm: {{ $connection->realm_id }}</div>
                @endif
            @else
                <span class="badge badge-red" style="font-size:0.85rem;padding:0.3rem 0.7rem">● Disconnected</span>
            @endif
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Last Sync</div>
        <div class="stat-value" style="font-size:1rem">
            @if($lastSync)
                {{ $lastSync->completed_at?->format('M j, Y g:i a') ?? 'In progress...' }} <span class="text-muted" style="font-size:0.75rem;font-weight:400">CST</span>
                <div class="text-sm text-muted" style="font-weight:400;margin-top:0.2rem">
                    {{ ucfirst($lastSync->direction->value) }} — {{ $lastSync->status->value }}
                </div>
            @else
                <span class="text-muted" style="font-size:0.9rem">Never synced</span>
            @endif
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Unresolved Conflicts</div>
        <div class="stat-value {{ $unresolvedCount > 0 ? 'gold' : '' }}">
            {{ $unresolvedCount }}
        </div>
        @if($unresolvedCount > 0)
        <div style="margin-top:0.5rem">
            <a href="{{ route('admin.qb.conflicts') }}" class="btn btn-primary btn-sm">Resolve Now</a>
        </div>
        @endif
    </div>
</div>

{{-- Loading overlay --}}
<div id="sync-overlay" style="display:none;position:fixed;inset:0;background:rgba(10,18,40,0.85);z-index:9999;flex-direction:column;align-items:center;justify-content:center;gap:1.25rem">
    <div style="width:52px;height:52px;border:4px solid rgba(201,168,76,0.25);border-top-color:var(--gold);border-radius:50%;animation:spin 0.9s linear infinite"></div>
    <div style="color:var(--gold);font-size:1.05rem;font-weight:600;font-family:'Playfair Display',serif">Syncing with QuickBooks…</div>
    <div style="color:var(--text-muted);font-size:0.85rem;max-width:320px;text-align:center">Importing members, payments, and pledges. This may take 1–2 minutes — please don't close this tab.</div>
</div>
<style>@keyframes spin{to{transform:rotate(360deg)}}</style>

{{-- Sync actions --}}
@if($isConnected)
<div class="card" style="margin-bottom:1.25rem">
    <div class="card-title">Sync Actions</div>
    <div style="display:flex;gap:1.25rem;flex-wrap:wrap;align-items:flex-start">
        {{-- Update sync (incremental — recent changes only) --}}
        <form method="POST" action="{{ route('admin.qb.sync.pull') }}" class="sync-form" data-label="update">
            @csrf
            <input type="hidden" name="forced" value="0">
            <div style="margin-bottom:0.5rem">
                <button type="submit" class="btn btn-primary sync-btn">↓ Update</button>
            </div>
            <div class="text-sm text-muted">Pull changes since last sync</div>
        </form>
        {{-- Full sync (forced — pulls everything) --}}
        <form method="POST" action="{{ route('admin.qb.sync.pull') }}" class="sync-form" data-label="full">
            @csrf
            <input type="hidden" name="forced" value="1">
            <div style="margin-bottom:0.5rem">
                <button type="submit" class="btn btn-outline sync-btn"
                    onclick="return confirm('Full sync imports all QB data and may take several minutes. Continue?')">
                    ↓ Full QB Sync
                </button>
            </div>
            <div class="text-sm text-muted">Re-import all customers, payments &amp; pledges</div>
        </form>
        <div style="width:1px;background:var(--border-dim)"></div>
        <form method="POST" action="{{ route('admin.qb.disconnect') }}" onsubmit="return confirm('Disconnect QuickBooks? Sync will stop until reconnected.')">
            @csrf
            <div style="margin-bottom:0.5rem">
                <button type="submit" class="btn btn-danger">Disconnect QB</button>
            </div>
            <div class="text-sm text-muted">Remove QuickBooks authorization</div>
        </form>
    </div>
</div>
@else
<div class="card" style="margin-bottom:1.25rem;border-color:rgba(201,168,76,0.3)">
    <div class="card-title">QuickBooks Not Connected</div>
    <p class="text-muted text-sm" style="margin-bottom:1rem">Connect your QuickBooks Online account to enable automatic member and payment sync.</p>
    <a href="{{ route('admin.qb.connect') }}" class="btn btn-gold">Connect QuickBooks →</a>
</div>
@endif

{{-- Recent sync log --}}
@if($recentLogs->count())
<div class="card">
    <div class="card-title">Recent Sync Activity</div>
    <table class="table">
        <thead>
            <tr>
                <th>Started</th>
                <th>Direction</th>
                <th>Status</th>
                <th>Families</th>
                <th>Payments</th>
                <th>Errors</th>
                <th>Duration</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentLogs as $log)
            <tr>
                <td class="text-sm text-muted">{{ $log->started_at?->format('M j g:i a') ?? '—' }} <span style="font-size:0.7rem;opacity:0.6">CST</span></td>
                <td><span class="badge badge-muted">{{ ucfirst($log->direction->value) }}</span></td>
                <td>
                    @if($log->status->value === 'completed')
                        <span class="badge badge-green">Completed</span>
                    @elseif($log->status->value === 'running')
                        <span class="badge badge-muted">Running…</span>
                    @elseif($log->status->value === 'failed')
                        <span class="badge badge-red">Failed</span>
                    @else
                        <span class="badge badge-muted">{{ ucfirst($log->status->value) }}</span>
                    @endif
                </td>
                <td class="text-sm text-muted">{{ $log->families_processed ?? 0 }}</td>
                <td class="text-sm text-muted">{{ $log->payments_processed ?? 0 }}</td>
                <td class="text-sm">
                    @if($log->errors && count($log->errors))
                        <span style="color:#f08080">{{ count($log->errors) }} error(s)</span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="text-sm text-muted">
                    @if($log->started_at && $log->completed_at)
                        {{ $log->started_at->diffForHumans($log->completed_at, true) }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<script>
document.querySelectorAll('.sync-form').forEach(function(form) {
    form.addEventListener('submit', function() {
        var overlay = document.getElementById('sync-overlay');
        overlay.style.display = 'flex';
        document.querySelectorAll('.sync-btn').forEach(function(btn) { btn.disabled = true; });
    });
});
</script>
@endsection
