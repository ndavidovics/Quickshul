@extends('layouts.admin')
@section('title', 'Members')

@section('content')
<div class="flex items-center justify-between" style="margin-bottom:1.5rem">
    <div>
        <h1 class="page-title">Members & Donors</h1>
        <p class="page-subtitle" style="margin-bottom:0">{{ $families->total() }} accounts</p>
    </div>
    <div style="display:flex;gap:0.5rem">
        <a href="{{ route('admin.members.import') }}" class="btn btn-outline">⬆ Import</a>
        <a href="{{ route('admin.members.export') }}?{{ http_build_query(request()->only(['search','membership_type','has_balance'])) }}"
           class="btn btn-outline">⬇ Export CSV</a>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.members') }}" style="margin-bottom:1.25rem">
    <div style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end">
        <div style="flex:2;min-width:200px">
            <input type="text" name="search" class="form-control" placeholder="Search name, email, city..." value="{{ request('search') }}">
        </div>
        <div style="min-width:160px">
            <select name="membership_type" class="form-control">
                <option value="">All types</option>
                <option value="all_members" {{ request('membership_type') === 'all_members' ? 'selected' : '' }}>All Members (excl. Donors)</option>
                @foreach($membershipTypes as $slug => $type)
                    <option value="{{ $slug }}" {{ request('membership_type') === $slug ? 'selected' : '' }}>
                        {{ $type->label }}
                    </option>
                @endforeach
            </select>
        </div>
        <label style="display:flex;align-items:center;gap:0.4rem;font-size:0.85rem;color:var(--text-muted);cursor:pointer;white-space:nowrap">
            <input type="checkbox" name="has_balance" value="1" {{ request('has_balance') ? 'checked' : '' }}>
            Has balance
        </label>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if(request()->hasAny(['search','membership_type','has_balance']))
            <a href="{{ route('admin.members') }}" class="btn btn-outline">Clear</a>
        @endif
    </div>
</form>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Family</th>
                <th>Address</th>
                <th>Emails</th>
                <th>Type</th>
                <th>Balance</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($families as $f)
            <tr>
                <td>
                    <div style="font-weight:500">{{ $f->name }}</div>
                    @if($f->phone)
                        <div class="text-sm text-muted">{{ $f->phone }}</div>
                    @endif
                </td>
                <td class="text-sm text-muted">
                    @if($f->address)
                        {{ $f->address }}<br>
                        {{ $f->city }}{{ $f->state ? ', '.$f->state : '' }} {{ $f->zip }}
                    @else
                        —
                    @endif
                </td>
                <td class="text-sm text-muted">
                    {{ $f->emails->pluck('email')->implode(', ') ?: '—' }}
                </td>
                <td><span class="badge badge-muted">{{ $membershipTypes[$f->membership_type]?->label ?? $f->membership_type }}</span></td>
                <td>
                    @if($f->outstanding_balance > 0)
                        <span style="color:var(--gold);font-weight:600">${{ number_format($f->outstanding_balance, 2) }}</span>
                    @else
                        <span class="badge badge-green">Current</span>
                    @endif
                </td>
                <td>
                    <div style="display:flex;gap:0.4rem">
                        <a href="{{ route('admin.members.show', $f->id) }}" class="btn btn-outline btn-sm">View</a>
                        <a href="{{ route('admin.members.edit', $f->id) }}" class="btn btn-primary btn-sm">Edit</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">No family accounts found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination">{{ $families->links() }}</div>
</div>
@endsection
