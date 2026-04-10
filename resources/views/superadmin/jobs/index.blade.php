@extends('superadmin.layout')
@section('title', 'Queue Jobs')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem">
    <div>
        <div class="page-title">Queue Jobs</div>
        <div class="page-subtitle">Background job status across all tenants</div>
    </div>
    @if($failedCount > 0)
    <form method="POST" action="{{ route('superadmin.jobs.flush') }}"
          onsubmit="return confirm('Delete ALL {{ $failedCount }} failed jobs? This cannot be undone.')">
        @csrf
        <button type="submit" class="btn btn-danger">Clear All Failed ({{ $failedCount }})</button>
    </form>
    @endif
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.5rem;max-width:360px">
    <div class="stat-card">
        <div class="stat-label">Pending Jobs</div>
        <div class="stat-value">{{ number_format($pendingCount) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Failed Jobs</div>
        <div class="stat-value" style="{{ $failedCount > 0 ? 'color:var(--error)' : '' }}">
            {{ number_format($failedCount) }}
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title">Failed Jobs</div>

    @if($failedJobs->isEmpty())
        <div style="text-align:center;padding:2.5rem;color:var(--text-muted)">
            <div style="font-size:2rem;margin-bottom:0.75rem">✅</div>
            <div style="font-weight:600;color:#fff;margin-bottom:0.3rem">No failed jobs</div>
            <div class="text-sm">All background jobs completed successfully.</div>
        </div>
    @else
    <div style="overflow-x:auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Job</th>
                    <th>Tenant</th>
                    <th>Failed At</th>
                    <th>Error</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($failedJobs as $job)
            <tr>
                <td style="font-family:monospace;font-size:0.8rem;color:#cde">{{ $job->job_name }}</td>
                <td class="text-sm">{{ $job->tenant_name }}</td>
                <td class="text-muted text-sm" style="white-space:nowrap">
                    {{ \Carbon\Carbon::parse($job->failed_at)->format('M j, Y H:i') }}
                </td>
                <td style="max-width:280px">
                    <div style="font-size:0.75rem;color:#f08080;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                         title="{{ e($job->exception) }}">
                        {{ $job->short_exception }}
                    </div>
                </td>
                <td style="white-space:nowrap">
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('superadmin.jobs.retry', $job->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-gold btn-sm">Retry</button>
                        </form>
                        <form method="POST" action="{{ route('superadmin.jobs.destroy', $job->id) }}"
                              onsubmit="return confirm('Delete this failed job?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($failedJobs->hasPages())
        <div class="pagination" style="margin-top:1rem">{{ $failedJobs->links() }}</div>
    @endif
    @endif
</div>
@endsection
