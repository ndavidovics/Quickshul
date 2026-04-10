@extends('superadmin.layout')
@section('title', 'System Health')

@section('content')
<div style="margin-bottom:1.75rem">
    <div class="page-title">System Health</div>
    <div class="page-subtitle">Live platform diagnostics — refreshes on page load</div>
</div>

{{-- Queue --}}
<div class="card">
    <div class="card-title">Queue</div>
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:1rem;max-width:320px;margin-bottom:{{ $failedJobs > 0 ? '1rem' : '0' }}">
        <div class="stat-card">
            <div class="stat-label">Pending</div>
            <div class="stat-value">{{ number_format($pendingJobs) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Failed</div>
            <div class="stat-value" style="{{ $failedJobs > 0 ? 'color:var(--error)' : '' }}">
                {{ number_format($failedJobs) }}
            </div>
        </div>
    </div>
    @if($failedJobs > 0)
        <a href="{{ route('superadmin.jobs.index') }}" class="btn btn-danger btn-sm">
            View {{ $failedJobs }} failed {{ Str::plural('job', $failedJobs) }} →
        </a>
    @endif
</div>

{{-- Storage --}}
<div class="card">
    <div class="card-title">Storage</div>
    @php
        $used    = $diskTotal - $diskFree;
        $usedPct = $diskTotal > 0 ? round(($used / $diskTotal) * 100) : 0;
        $barColor = $usedPct > 85 ? 'var(--error)' : ($usedPct > 70 ? '#f39c12' : '#2ecc71');
    @endphp
    <div style="margin-bottom:1.25rem">
        <div style="display:flex;justify-content:space-between;font-size:0.82rem;margin-bottom:0.4rem">
            <span class="text-muted">Disk Usage</span>
            <span>{{ $usedPct }}%
                &nbsp;·&nbsp; {{ number_format($used / 1073741824, 1) }} GB used
                of {{ number_format($diskTotal / 1073741824, 1) }} GB
                &nbsp;·&nbsp; {{ number_format($diskFree / 1073741824, 1) }} GB free
            </span>
        </div>
        <div style="background:rgba(255,255,255,0.08);border-radius:4px;height:8px;overflow:hidden">
            <div style="width:{{ $usedPct }}%;height:100%;background:{{ $barColor }};border-radius:4px;transition:width 0.3s"></div>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:0.75rem;font-size:0.83rem">
        <span class="text-muted">Laravel log:</span>
        <span>{{ $logSize > 1048576
            ? number_format($logSize / 1048576, 1) . ' MB'
            : number_format($logSize / 1024, 1) . ' KB' }}</span>
        @if($logSize > 52428800)
            <span class="badge badge-red">Large — consider rotating</span>
        @endif
    </div>
</div>

{{-- Database --}}
<div class="card">
    <div class="card-title">Database Tables</div>
    <p class="text-sm text-muted" style="margin-bottom:1rem">Row counts are approximate (InnoDB estimates).</p>
    <table class="table">
        <thead>
            <tr><th>Table</th><th>Rows (approx)</th><th>Size</th></tr>
        </thead>
        <tbody>
        @foreach($tableSizes as $t)
        <tr>
            <td style="font-family:monospace;font-size:0.83rem">{{ $t->table_name }}</td>
            <td>{{ number_format($t->table_rows) }}</td>
            <td class="text-muted text-sm">{{ $t->size_mb }} MB</td>
        </tr>
        @endforeach
        @if(empty($tableSizes))
            <tr><td colspan="3" class="text-muted text-sm" style="text-align:center;padding:1rem">No data</td></tr>
        @endif
        </tbody>
    </table>
</div>

{{-- App Info --}}
<div class="card">
    <div class="card-title">App Info</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.875rem">
        @foreach($appInfo as $label => $value)
        <div>
            <div class="text-muted text-sm" style="margin-bottom:0.2rem">{{ $label }}</div>
            <div style="font-family:monospace;font-size:0.85rem;color:{{ str_contains($value, '⚠️') ? '#f5c76b' : '#fff' }}">{{ $value }}</div>
        </div>
        @endforeach
    </div>
</div>
@endsection
