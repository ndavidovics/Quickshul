@extends('layouts.admin')
@section('title', 'Edit — '.$family->name)

@section('content')
<div class="flex items-center gap-3" style="margin-bottom:1rem">
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
                    <option value="{{ $type->slug }}" {{ $family->membership_type === $type->slug ? 'selected' : '' }}>{{ $type->label }}</option>
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
    @php $u = $usersByEmail[$e->email] ?? null; @endphp
    <div style="display:flex;align-items:center;justify-content:space-between;gap:0.75rem;padding:0.5rem 0;border-bottom:1px solid var(--border-dim)">
        <div style="min-width:0">
            <div style="display:flex;align-items:center;gap:0.4rem;flex-wrap:wrap">
                @if($u?->avatar)
                    <img src="{{ $u->avatar }}" alt="" style="width:18px;height:18px;border-radius:50%;border:1px solid var(--border-dim);flex-shrink:0">
                @endif
                <span>{{ $e->email }}</span>
                @if($e->is_primary) <span class="badge badge-gold">Primary</span> @endif
            </div>
            <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.2rem">
                @if($u)
                    @if($u->last_login_at)
                        Last login: {{ $u->last_login_at->format('M j, Y g:i A') }} &middot; {{ $u->last_login_at->diffForHumans() }}
                    @else
                        Never logged in
                    @endif
                @else
                    No portal account
                @endif
            </div>
        </div>
        <form method="POST" action="{{ route('admin.members.remove-email', [$family->id, $e->id]) }}" onsubmit="return confirm('Remove this email?')" style="flex-shrink:0">
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
        <thead><tr><th>Name</th><th>Role</th><th>DOB</th><th>Hebrew DOB</th><th></th></tr></thead>
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
            </div>
            <button type="submit" class="btn btn-primary">Add Member</button>
        </form>
    </div>
</div>

{{-- Yahrtzeits --}}
<div class="card" style="margin-top:1.25rem">
    <div class="flex items-center justify-between" style="margin-bottom:1rem">
        <div class="card-title" style="margin-bottom:0;border-bottom:none;padding-bottom:0">Yahrtzeits</div>
    </div>

    @if($family->yahrtzeits->isEmpty())
        <p class="text-muted text-sm">No yahrtzeits on record for this family.</p>
    @else
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Hebrew Name</th>
                <th>Relationship</th>
                <th>Date of Death</th>
                <th>Annual Yahrzeit</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($family->yahrtzeits as $y)
            <tr>
                <td style="font-weight:500">{{ $y->full_name }}</td>
                <td style="direction:rtl;font-family:serif">{{ $y->hebrew_name ?? '—' }}</td>
                <td>{{ $y->relationship_label ?? '—' }}</td>
                <td class="text-muted text-sm">{{ $y->date_of_death?->format('M j, Y') ?? '—' }}</td>
                <td class="text-sm">
                    @php
                        $months = [1=>'Tishrei',2=>'Cheshvan',3=>'Kislev',4=>'Tevet',5=>'Shevat',
                                   6=>'Adar I',7=>'Adar/Adar II',8=>'Nisan',9=>'Iyar',10=>'Sivan',
                                   11=>'Tammuz',12=>'Av',13=>'Elul'];
                    @endphp
                    {{ $y->hebrew_day }} {{ $months[$y->hebrew_month] ?? '' }}
                    @if($y->hebrew_date_of_death)
                        <div class="text-muted" style="font-size:0.75rem">{{ $y->hebrew_date_of_death }}</div>
                    @endif
                </td>
                <td>
                    <div style="display:flex;gap:0.4rem">
                        <a href="{{ route('admin.yahrtzeits.edit', [$family->id, $y->id]) }}" class="btn btn-outline btn-sm">Edit</a>
                        <form method="POST" action="{{ route('admin.yahrtzeits.destroy', [$family->id, $y->id]) }}" onsubmit="return confirm('Remove this yahrtzeit?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Add Yahrtzeit form --}}
    <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border-dim)">
        <div style="font-size:0.85rem;font-weight:600;color:var(--gold);margin-bottom:1rem">Add Yahrtzeit</div>
        <form method="POST" action="{{ route('admin.yahrtzeits.store', $family->id) }}">
            @csrf
            <div class="grid-2">
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label">Full Name <span style="color:var(--gold)">*</span></label>
                    <input type="text" name="full_name" class="form-control" required placeholder="e.g. Moshe Cohen">
                </div>
                <div class="form-group">
                    <label class="form-label">Hebrew Name</label>
                    <input type="text" name="hebrew_name" class="form-control heb-input"
                           style="direction:rtl;font-family:serif" placeholder="שם עברי">
                </div>
                <div class="form-group">
                    <label class="form-label">Relationship to Family <span class="text-muted text-sm">(optional)</span></label>
                    <select name="relationship" class="form-control">
                        <option value="">— None —</option>
                        @foreach(['mother','father','sister','brother','child','spouse'] as $rel)
                        <option value="{{ $rel }}">{{ ucfirst($rel) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Linked Family Member(s) <span class="text-muted text-sm">(optional)</span></label>
                    <select name="family_member_ids[]" class="form-control" multiple>
                        @foreach($family->members as $m)
                        <option value="{{ $m->id }}">{{ $m->full_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border-dim)">
                <div style="font-size:0.8rem;font-weight:600;color:var(--text-muted);margin-bottom:0.75rem">Date of Death</div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Gregorian Date <span class="text-muted text-sm">(auto-computes Hebrew)</span></label>
                        <input type="date" name="date_of_death" class="form-control" id="add-greg-date">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hebrew Date (full) <span class="text-muted text-sm">(auto-computed)</span></label>
                        <input type="text" name="hebrew_date_of_death" class="form-control" placeholder="e.g. 15 Tishrei 5785" id="add-heb-date">
                    </div>
                </div>
            </div>

            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border-dim)">
                <div style="font-size:0.8rem;font-weight:600;color:var(--text-muted);margin-bottom:0.25rem">Annual Yahrtzeit <span style="font-weight:400">(Hebrew Month &amp; Day)</span></div>
                <p class="text-muted text-sm" style="margin-bottom:0.75rem">Required. Auto-filled when you enter a Gregorian date above.</p>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Hebrew Month <span style="color:var(--gold)">*</span></label>
                        <select name="hebrew_month" class="form-control" id="add-heb-month">
                            <option value="">— Select —</option>
                            @foreach([1=>'Tishrei',2=>'Cheshvan',3=>'Kislev',4=>'Tevet',5=>'Shevat',6=>'Adar I',7=>'Adar / Adar II',8=>'Nisan',9=>'Iyar',10=>'Sivan',11=>'Tammuz',12=>'Av',13=>'Elul'] as $num => $name)
                            <option value="{{ $num }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hebrew Day <span style="color:var(--gold)">*</span></label>
                        <select name="hebrew_day" class="form-control" id="add-heb-day">
                            <option value="">— Select —</option>
                            @for($d = 1; $d <= 30; $d++)
                            <option value="{{ $d }}">{{ $d }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top:1rem">Add Yahrtzeit</button>
        </form>
    </div>
</div>

{{-- Hebrew Keyboard for admin edit page --}}
@include('admin.yahrtzeits._hebrew_keyboard')

<script>
// Auto-fill Hebrew month/day via AJAX when Gregorian date is entered
document.getElementById('add-greg-date').addEventListener('change', function () {
    var date = this.value;
    if (!date) return;
    fetch('/admin/yahrtzeits/hebrew-date?date=' + encodeURIComponent(date), {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.month) document.getElementById('add-heb-month').value = data.month;
        if (data.day)   document.getElementById('add-heb-day').value   = data.day;
        if (data.full)  document.getElementById('add-heb-date').value  = data.full;
    });
});
</script>
@endsection
