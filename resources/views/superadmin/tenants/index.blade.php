@extends('superadmin.layout')
@section('title', 'Tenants')

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <div class="page-title">Tenants</div>
        <div class="page-subtitle">All organizations registered on QuickShul ({{ $tenants->total() }} total)</div>
    </div>
</div>

{{-- Global Stats --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:1rem;margin-bottom:1.5rem">
    <div class="stat-card">
        <div class="stat-label">Active Shuls</div>
        <div class="stat-value gold">{{ number_format($globalStats['total_active']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Families</div>
        <div class="stat-value">{{ number_format($globalStats['total_families']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Users</div>
        <div class="stat-value">{{ number_format($globalStats['total_users']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Payments</div>
        <div class="stat-value">{{ number_format($globalStats['total_payments']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">New This Month</div>
        <div class="stat-value">{{ number_format($globalStats['new_this_month']) }}</div>
    </div>
</div>

<div class="card">
    <div style="overflow-x:auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Org Name</th>
                    <th>Slug / Portal</th>
                    <th>Status</th>
                    <th>Members</th>
                    <th>Users</th>
                    <th>Gmail</th>
                    <th>PayPal</th>
                    <th>QB</th>
                    <th>QB Sync</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $t)
                <tr>
                    <td>
                        <div style="font-weight:600;color:#fff">{{ $t->name }}</div>
                        @if($t->tagline)
                            <div class="text-sm text-muted">{{ $t->tagline }}</div>
                        @endif
                        @if($t->deleted_at)
                            <div class="text-sm" style="color:#f08080">Deleted {{ $t->deleted_at->diffForHumans() }}</div>
                        @endif
                    </td>
                    <td>
                        <a href="https://{{ $t->slug }}.quickshul.com" target="_blank"
                           style="color:var(--gold);text-decoration:none;font-size:0.82rem">
                            {{ $t->slug }}.quickshul.com ↗
                        </a>
                    </td>
                    <td>
                        @if($t->status === 'active')
                            <span class="badge badge-green">Active</span>
                        @elseif($t->status === 'pending')
                            <span class="badge badge-yellow">Pending</span>
                        @elseif($t->status === 'suspended')
                            <span class="badge badge-red">Suspended</span>
                        @else
                            <span class="badge badge-muted">{{ $t->status }}</span>
                        @endif
                    </td>
                    <td>{{ number_format($t->families_count) }}</td>
                    <td>{{ number_format($t->users_count) }}</td>
                    <td style="text-align:center">{{ $t->isGmailConnected() ? '✅' : '❌' }}</td>
                    <td style="text-align:center">{{ $t->isPayPalConnected() ? '✅' : '❌' }}</td>
                    <td style="text-align:center">{{ $t->qb_enabled ? '✅' : '❌' }}</td>
                    <td style="text-align:center">
                        @if(in_array($t->id, $syncedTenantIds))
                            <span class="badge badge-green">✓ Synced</span>
                        @elseif($t->qb_enabled)
                            <span class="badge badge-yellow">Never</span>
                        @else
                            <span class="text-muted text-sm">—</span>
                        @endif
                    </td>
                    <td class="text-muted text-sm" style="white-space:nowrap">{{ $t->created_at->format('M j, Y') }}</td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('superadmin.tenants.show', $t->id) }}" class="btn btn-outline btn-sm">View</a>
                            @if($t->status !== 'active' && !$t->deleted_at)
                            <form method="POST" action="{{ route('superadmin.tenants.activate', $t->id) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="btn btn-gold btn-sm">Activate</button>
                            </form>
                            @endif
                            @if($t->status === 'active' && !$t->deleted_at)
                            <form method="POST" action="{{ route('superadmin.tenants.suspend', $t->id) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm">Suspend</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="text-align:center;padding:2rem;color:var(--text-muted)">
                        No tenants found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tenants->hasPages())
        <div class="pagination">
            {{ $tenants->links() }}
        </div>
    @endif
</div>
@endsection
