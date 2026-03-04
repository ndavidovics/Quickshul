@extends('layouts.admin')
@section('title', 'Edit Member — '.$member->full_name)

@section('content')
<div class="flex items-center gap-3" style="margin-bottom:1.5rem">
    <a href="{{ route('admin.members.edit', $family->id) }}" class="btn btn-outline btn-sm">← Back to {{ $family->name }}</a>
    <h1 class="page-title" style="margin-bottom:0">Edit Member: {{ $member->full_name }}</h1>
</div>

@if($errors->any())
<div style="background:rgba(240,128,128,0.1);border:1px solid rgba(240,128,128,0.4);border-radius:6px;padding:1rem;margin-bottom:1.25rem;color:#f08080;font-size:0.875rem">
    @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
</div>
@endif

<div class="card" style="max-width:720px">
    <div class="card-title">Personal Information</div>
    <form method="POST" action="{{ route('admin.members.update-member', [$family->id, $member->id]) }}">
        @csrf @method('PUT')
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $member->first_name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $member->last_name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Hebrew Name</label>
                <input type="text" name="hebrew_name" class="form-control" placeholder="e.g. אברהם בן משה" value="{{ old('hebrew_name', $member->hebrew_name) }}" style="direction:rtl">
            </div>
            <div class="form-group">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-control">
                    <option value="male" {{ old('gender', $member->gender) === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('gender', $member->gender) === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ old('gender', $member->gender) === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" class="form-control">
                    <option value="parent" {{ old('role', $member->role) === 'parent' ? 'selected' : '' }}>Parent / Adult</option>
                    <option value="child" {{ old('role', $member->role) === 'child' ? 'selected' : '' }}>Child</option>
                    <option value="other" {{ old('role', $member->role) === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
        </div>

        <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--border-dim)">
            <div style="font-size:0.85rem;font-weight:600;color:var(--gold);margin-bottom:1rem">Date of Birth</div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Date of Birth (Gregorian)</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $member->date_of_birth?->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">
                        Hebrew DOB
                        @if($member->hebrew_dob_override)
                            <span class="badge badge-gold" style="font-size:0.6rem;margin-left:0.3rem">locked</span>
                        @else
                            <span class="text-muted" style="font-size:0.7rem;font-weight:400">(auto-computed)</span>
                        @endif
                    </label>
                    <input type="text" name="hebrew_date_of_birth" class="form-control"
                           placeholder="e.g. 15 Tishrei 5785"
                           value="{{ old('hebrew_date_of_birth', $member->hebrew_date_of_birth) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex;align-items:center;gap:0.4rem">
                        <input type="checkbox" name="hebrew_dob_override" value="1" {{ old('hebrew_dob_override', $member->hebrew_dob_override) ? 'checked' : '' }}>
                        Lock Hebrew DOB (override auto-compute)
                    </label>
                </div>
            </div>
        </div>

        <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--border-dim)">
            <div style="font-size:0.85rem;font-weight:600;color:var(--gold);margin-bottom:1rem">Date of Death <span class="text-muted" style="font-weight:400;font-size:0.75rem">(leave blank if living)</span></div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Date of Death (Gregorian)</label>
                    <input type="date" name="date_of_death" class="form-control" value="{{ old('date_of_death', $member->date_of_death?->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">
                        Hebrew DOD (Yahrzeit)
                        @if($member->hebrew_dod_override)
                            <span class="badge badge-gold" style="font-size:0.6rem;margin-left:0.3rem">locked</span>
                        @else
                            <span class="text-muted" style="font-size:0.7rem;font-weight:400">(auto-computed)</span>
                        @endif
                    </label>
                    <input type="text" name="hebrew_date_of_death" class="form-control"
                           placeholder="e.g. 15 Tishrei 5785"
                           value="{{ old('hebrew_date_of_death', $member->hebrew_date_of_death) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex;align-items:center;gap:0.4rem">
                        <input type="checkbox" name="hebrew_dod_override" value="1" {{ old('hebrew_dod_override', $member->hebrew_dod_override) ? 'checked' : '' }}>
                        Lock Hebrew DOD (override auto-compute)
                    </label>
                </div>
            </div>
        </div>

        <div style="margin-top:1.5rem;display:flex;gap:0.75rem">
            <button type="submit" class="btn btn-gold">Save Member</button>
            <a href="{{ route('admin.members.edit', $family->id) }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
