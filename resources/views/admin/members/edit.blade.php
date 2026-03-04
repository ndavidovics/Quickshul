@extends('layouts.admin')
@section('title', 'Edit — '.$family->name)

@section('content')
<div class="flex items-center gap-3" style="margin-bottom:1.5rem">
    <a href="{{ route('admin.members.show', $family->id) }}" class="btn btn-outline btn-sm">← Back</a>
    <h1 class="page-title" style="margin-bottom:0">Edit: {{ $family->name }}</h1>
</div>

<div class="grid-2">

{{-- Family edit form --}}
<div class="card">
    <div class="card-title">Family Details</div>
    <form method="POST" action="{{ route('admin.members.update', $family->id) }}">
        @csrf @method('PUT')
        <div class="grid-2">
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Family Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $family->name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Membership Type</label>
                <select name="membership_type" class="form-control">
                    @foreach($membershipTypes as $type)
                    <option value="{{ $type->value }}" {{ $family->membership_type->value === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Member Since</label>
                <input type="date" name="member_since" class="form-control" value="{{ old('member_since', $family->member_since?->format('Y-m-d')) }}">
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $family->address) }}">
            </div>
            <div class="form-group">
                <label class="form-label">City</label>
                <input type="text" name="city" class="form-control" value="{{ old('city', $family->city) }}">
            </div>
            <div class="form-group">
                <label class="form-label">State</label>
                <input type="text" name="state" class="form-control" maxlength="2" value="{{ old('state', $family->state) }}">
            </div>
            <div class="form-group">
                <label class="form-label">ZIP</label>
                <input type="text" name="zip" class="form-control" value="{{ old('zip', $family->zip) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $family->phone) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Total Pledged ($)</label>
                <input type="number" name="total_pledged" class="form-control" step="0.01" min="0" value="{{ old('total_pledged', $family->total_pledged) }}">
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $family->notes) }}</textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-gold">Save Family</button>
    </form>
</div>

{{-- Email addresses --}}
<div class="card">
    <div class="card-title">Login Emails</div>
    @foreach($family->emails as $e)
    <div style="display:flex;align-items:center;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid var(--border-dim)">
        <div>
            <span>{{ $e->email }}</span>
            @if($e->is_primary) <span class="badge badge-gold" style="margin-left:0.4rem">Primary</span> @endif
        </div>
        <form method="POST" action="{{ route('admin.members.remove-email', [$family->id, $e->id]) }}" onsubmit="return confirm('Remove this email?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Remove</button>
        </form>
    </div>
    @endforeach
    <form method="POST" action="{{ route('admin.members.add-email', $family->id) }}" style="display:flex;gap:0.75rem;margin-top:1rem;align-items:flex-end">
        @csrf
        <div class="form-group" style="flex:1;margin-bottom:0">
            <label class="form-label">Add Email</label>
            <input type="email" name="email" class="form-control" placeholder="email@example.com">
        </div>
        <button type="submit" class="btn btn-primary">Add</button>
    </form>
</div>

</div>

{{-- Family members --}}
<div class="card" style="margin-top:1.25rem">
    <div class="flex items-center justify-between" style="margin-bottom:1rem">
        <div class="card-title" style="margin-bottom:0;border-bottom:none;padding-bottom:0">Family Members</div>
    </div>
    <table class="table">
        <thead><tr><th>Name</th><th>Role</th><th>DOB</th><th>Hebrew DOB</th><th>Hebrew DOD</th><th></th></tr></thead>
        <tbody>
            @foreach($family->members as $m)
            <tr>
                <td>
                    <div style="font-weight:500">{{ $m->full_name }}</div>
                    @if($m->hebrew_name) <div style="font-size:0.85rem;direction:rtl;color:var(--text-muted)">{{ $m->hebrew_name }}</div> @endif
                </td>
                <td><span class="badge badge-muted">{{ ucfirst($m->role) }}</span></td>
                <td class="text-muted text-sm">{{ $m->date_of_birth?->format('M j, Y') ?? '—' }}</td>
                <td class="text-sm">{{ $m->hebrew_date_of_birth ?? '—' }} @if($m->hebrew_dob_override) <span class="badge badge-gold" style="font-size:0.6rem">override</span> @endif</td>
                <td class="text-sm">{{ $m->hebrew_date_of_death ?? '—' }} @if($m->hebrew_dod_override) <span class="badge badge-gold" style="font-size:0.6rem">override</span> @endif</td>
                <td>
                    <div style="display:flex;gap:0.4rem">
                        <a href="{{ route('admin.members.edit-member', [$family->id, $m->id]) }}" class="btn btn-outline btn-sm">Edit</a>
                        <form method="POST" action="{{ route('admin.members.delete-member', [$family->id, $m->id]) }}" onsubmit="return confirm('Remove this member?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Add member form --}}
    <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border-dim)">
        <div style="font-size:0.85rem;font-weight:600;color:var(--gold);margin-bottom:1rem">Add Family Member</div>
        <form method="POST" action="{{ route('admin.members.add-member', $family->id) }}">
            @csrf
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Hebrew Name</label>
                    <input type="text" name="hebrew_name" class="form-control" placeholder="e.g. אברהם בן משה">
                </div>
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-control"><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control"><option value="parent">Parent / Adult</option><option value="child">Child</option><option value="other">Other</option></select>
                </div>
                <div class="form-group">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Hebrew DOB Override <span class="text-muted" style="font-size:0.7rem;font-weight:400">(leave blank to auto-compute)</span></label>
                    <input type="text" name="hebrew_date_of_birth" class="form-control" placeholder="e.g. 15 Tishrei 5785">
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex;align-items:center;gap:0.4rem">
                        <input type="checkbox" name="hebrew_dob_override" value="1"> Lock Hebrew DOB
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label">Date of Death</label>
                    <input type="date" name="date_of_death" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Hebrew DOD Override</label>
                    <input type="text" name="hebrew_date_of_death" class="form-control" placeholder="e.g. 15 Tishrei 5785">
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex;align-items:center;gap:0.4rem">
                        <input type="checkbox" name="hebrew_dod_override" value="1"> Lock Hebrew DOD
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Add Member</button>
        </form>
    </div>
</div>
@endsection
