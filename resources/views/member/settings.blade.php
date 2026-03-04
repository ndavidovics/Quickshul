@extends('layouts.app')
@section('title', 'Account Settings')

@section('content')
<h1 class="page-title">Account Settings</h1>
<p class="page-subtitle">Manage your portal account</p>

<div class="grid-2">

    {{-- Account info --}}
    <div class="card">
        <div class="card-title">Account Information</div>
        <div style="display:flex;flex-direction:column;gap:0.75rem">
            <div>
                <div class="text-sm text-muted" style="margin-bottom:0.2rem">Name</div>
                <div style="font-weight:500">{{ auth()->user()->name }}</div>
            </div>
            <div>
                <div class="text-sm text-muted" style="margin-bottom:0.2rem">Email</div>
                <div style="font-weight:500">{{ auth()->user()->email }}</div>
            </div>
            @if(auth()->user()->google_id)
            <div>
                <span class="badge badge-blue">Google Account</span>
                <div class="text-sm text-muted" style="margin-top:0.35rem">Signed in via Google — no portal password required.</div>
            </div>
            @endif
            @if($family)
            <div>
                <div class="text-sm text-muted" style="margin-bottom:0.2rem">Family Account</div>
                <div style="font-weight:500">{{ $family->name }}</div>
                <div class="text-sm text-muted">{{ $family->membership_type->label() }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Change password --}}
    @if(!auth()->user()->google_id || auth()->user()->password)
    <div class="card">
        <div class="card-title">Change Password</div>
        <form method="POST" action="{{ route('settings.password') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
                @error('current_password') <div style="color:#f08080;font-size:0.78rem;margin-top:0.3rem">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required minlength="8">
            </div>
            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </div>
    @endif

    {{-- Linked emails --}}
    @if($family)
    <div class="card">
        <div class="card-title">Linked Email Addresses</div>
        <p class="text-sm text-muted" style="margin-bottom:1rem">These emails can be used to log into this family account. To add or remove emails, contact the synagogue office.</p>
        @foreach($family->emails as $e)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid var(--border-dim)">
            <span>{{ $e->email }}</span>
            @if($e->is_primary) <span class="badge badge-gold">Primary</span> @endif
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
