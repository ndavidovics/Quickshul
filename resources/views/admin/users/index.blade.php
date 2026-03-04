@extends('layouts.admin')
@section('title', 'User Management')

@section('content')
<div class="flex items-center justify-between" style="margin-bottom:1.5rem">
    <div>
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle" style="margin-bottom:0">{{ $users->total() }} portal accounts</p>
    </div>
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

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Auth</th>
                <th>Family Account</th>
                <th>Last Login</th>
                <th>Role</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>
                    <div style="font-weight:500">{{ $user->name }}</div>
                    @if($user->id === auth()->id())
                        <span class="badge badge-muted" style="font-size:0.6rem">you</span>
                    @endif
                </td>
                <td class="text-sm text-muted">{{ $user->email }}</td>
                <td>
                    @if($user->google_id)
                        <span class="badge badge-blue" style="font-size:0.7rem">Google</span>
                    @else
                        <span class="badge badge-muted" style="font-size:0.7rem">Password</span>
                    @endif
                </td>
                <td class="text-sm">
                    @if($user->family)
                        <a href="{{ route('admin.members.show', $user->family->id) }}" style="color:var(--gold)">
                            {{ $user->family->name }}
                        </a>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="text-sm text-muted">
                    {{ $user->updated_at->format('M j, Y') }}
                </td>
                <td>
                    @if($user->is_admin)
                        <span class="badge badge-gold">Admin</span>
                    @else
                        <span class="badge badge-muted">Member</span>
                    @endif
                </td>
                <td>
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.toggle', $user->id) }}"
                          onsubmit="return confirm('{{ $user->is_admin ? 'Remove admin role from' : 'Grant admin role to' }} {{ addslashes($user->name) }}?')">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $user->is_admin ? 'btn-danger' : 'btn-outline' }}">
                            {{ $user->is_admin ? 'Remove Admin' : 'Make Admin' }}
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination">{{ $users->links() }}</div>
</div>
@endsection
