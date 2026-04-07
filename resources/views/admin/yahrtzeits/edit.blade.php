@extends('layouts.admin')
@section('title', 'Edit Yahrtzeit — '.$yahrtzeit->full_name)

@section('content')
<div class="flex items-center gap-3" style="margin-bottom:1.5rem">
    <a href="{{ route('admin.members.edit', $family->id) }}" class="btn btn-outline btn-sm">← Back to {{ $family->name }}</a>
    <h1 class="page-title" style="margin-bottom:0">Edit Yahrtzeit: {{ $yahrtzeit->full_name }}</h1>
</div>

@if($errors->any())
<div style="background:rgba(240,128,128,0.1);border:1px solid rgba(240,128,128,0.4);border-radius:6px;padding:1rem;margin-bottom:1.25rem;color:#f08080;font-size:0.875rem">
    @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
</div>
@endif

<div class="card" style="max-width:720px">
    <div class="card-title">Yahrtzeit Information</div>
    <form method="POST" action="{{ route('admin.yahrtzeits.update', [$family->id, $yahrtzeit->id]) }}">
        @csrf @method('PUT')

        <div class="grid-2">
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Full Name <span style="color:var(--gold)">*</span></label>
                <input type="text" name="full_name" class="form-control" required
                       value="{{ old('full_name', $yahrtzeit->full_name) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Hebrew Name</label>
                <input type="text" name="hebrew_name" class="form-control heb-input"
                       value="{{ old('hebrew_name', $yahrtzeit->hebrew_name) }}"
                       style="direction:rtl;font-family:serif" placeholder="שם עברי">
            </div>
            <div class="form-group">
                <label class="form-label">Relationship to Family <span class="text-muted text-sm">(optional)</span></label>
                <select name="relationship" class="form-control">
                    <option value="">— None —</option>
                    @foreach(['mother','father','sister','brother','child','spouse'] as $rel)
                    <option value="{{ $rel }}" {{ old('relationship', $yahrtzeit->relationship) === $rel ? 'selected' : '' }}>{{ ucfirst($rel) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Linked Families <span style="color:var(--gold)">*</span></label>
                @php $linkedFamilyIds = old('family_ids', $yahrtzeit->families->pluck('id')->toArray()); @endphp
                <select name="family_ids[]" id="edit-family-select" class="form-control" multiple required>
                    @foreach($allFamilies as $f)
                    <option value="{{ $f->id }}" {{ in_array($f->id, $linkedFamilyIds) ? 'selected' : '' }}>{{ $f->name }}</option>
                    @endforeach
                </select>
                <div class="text-muted text-sm" style="margin-top:0.25rem">Type to search and select all families this yahrtzeit should appear for.</div>
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Linked Family Members <span class="text-muted text-sm">(optional)</span></label>
                @php
                    $linkedMemberIds  = old('family_member_ids', $yahrtzeit->familyMembers->pluck('id')->toArray());
                    $availableMembers = $yahrtzeit->families->map->members->flatten()->unique('id')->sortBy('full_name');
                @endphp
                <select name="family_member_ids[]" id="edit-member-select" class="form-control" multiple>
                    @foreach($availableMembers as $m)
                    <option value="{{ $m->id }}" {{ in_array($m->id, $linkedMemberIds) ? 'selected' : '' }}>{{ $m->full_name }} ({{ $m->family->name ?? '' }})</option>
                    @endforeach
                </select>
                <div class="text-muted text-sm" style="margin-top:0.25rem">Select which individual members within the linked families should receive this yahrtzeit reminder.</div>
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $yahrtzeit->notes) }}</textarea>
            </div>
        </div>

        <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--border-dim)">
            <div style="font-size:0.85rem;font-weight:600;color:var(--gold);margin-bottom:1rem">Date of Death</div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Gregorian Date <span class="text-muted text-sm">(optional — auto-computes Hebrew)</span></label>
                    <input type="date" name="date_of_death" class="form-control"
                           value="{{ old('date_of_death', $yahrtzeit->date_of_death?->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">
                        Hebrew Date (full)
                        @if($yahrtzeit->hebrew_dod_override)
                            <span class="badge badge-gold" style="font-size:0.6rem;margin-left:0.3rem">locked</span>
                        @else
                            <span class="text-muted" style="font-size:0.7rem;font-weight:400">(auto-computed)</span>
                        @endif
                    </label>
                    <input type="text" name="hebrew_date_of_death" class="form-control"
                           placeholder="e.g. 15 Tishrei 5785"
                           value="{{ old('hebrew_date_of_death', $yahrtzeit->hebrew_date_of_death) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex;align-items:center;gap:0.4rem">
                        <input type="checkbox" name="hebrew_dod_override" value="1"
                               {{ old('hebrew_dod_override', $yahrtzeit->hebrew_dod_override) ? 'checked' : '' }}>
                        Lock Hebrew date (override auto-compute)
                    </label>
                </div>
            </div>
        </div>

        <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--border-dim)">
            <div style="font-size:0.85rem;font-weight:600;color:var(--gold);margin-bottom:0.5rem">Annual Yahrtzeit Date <span class="text-muted" style="font-weight:400;font-size:0.75rem">(required — auto-filled from Gregorian date above)</span></div>
            <p class="text-muted text-sm" style="margin-bottom:1rem">This is the Hebrew month and day observed each year. Auto-calculated if you enter a Gregorian date above.</p>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Hebrew Month <span style="color:var(--gold)">*</span></label>
                    <select name="hebrew_month" class="form-control">
                        <option value="">— Select Month —</option>
                        @foreach([1=>'Tishrei',2=>'Cheshvan',3=>'Kislev',4=>'Tevet',5=>'Shevat',6=>'Adar I',7=>'Adar / Adar II',8=>'Nisan',9=>'Iyar',10=>'Sivan',11=>'Tammuz',12=>'Av',13=>'Elul'] as $num => $name)
                        <option value="{{ $num }}" {{ old('hebrew_month', $yahrtzeit->hebrew_month) == $num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Hebrew Day <span style="color:var(--gold)">*</span></label>
                    <select name="hebrew_day" class="form-control">
                        <option value="">— Select Day —</option>
                        @for($d = 1; $d <= 30; $d++)
                        <option value="{{ $d }}" {{ old('hebrew_day', $yahrtzeit->hebrew_day) == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>

        <div style="margin-top:1.5rem;display:flex;gap:0.75rem">
            <button type="submit" class="btn btn-gold">Save Yahrtzeit</button>
            <a href="{{ route('admin.members.edit', $family->id) }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

{{-- Hebrew Keyboard --}}
@include('admin.yahrtzeits._hebrew_keyboard')
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.min.css" rel="stylesheet">
<style>.ts-wrapper { font-size: 0.875rem; }</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new TomSelect('#edit-family-select', {
        plugins: ['remove_button'],
        placeholder: 'Type to search families…',
        maxOptions: 300,
    });
    new TomSelect('#edit-member-select', {
        plugins: ['remove_button'],
        placeholder: 'Type to search members…',
        maxOptions: 500,
    });
});
</script>
@endsection
