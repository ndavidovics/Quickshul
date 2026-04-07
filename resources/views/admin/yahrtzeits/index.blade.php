@extends('layouts.admin')
@section('title', 'Yahrtzeits')

@section('content')
<div class="flex items-center justify-between" style="margin-bottom:1.5rem">
    <div>
        <h1 class="page-title">Yahrtzeits</h1>
        <p class="page-subtitle" style="margin-bottom:0">{{ $yahrtzeits->total() }} records</p>
    </div>
    <div style="display:flex;gap:0.5rem">
        <a href="{{ route('admin.yahrtzeits.export') }}?{{ http_build_query(request()->only(['search','has_dod'])) }}"
           class="btn btn-outline">⬇ Export CSV</a>
        <button class="btn btn-primary" onclick="document.getElementById('add-form').classList.toggle('hidden')">+ Add Yahrtzeit</button>
    </div>
</div>

{{-- Upcoming Yahrtzeits --}}
@if($upcoming->isNotEmpty())
<div class="card" style="margin-bottom:1.25rem;border-left:4px solid var(--gold)">
    <div class="card-title" style="margin-bottom:0.75rem">Upcoming Yahrtzeits — Next 14 Days</div>
    <div style="display:flex;flex-wrap:wrap;gap:0.5rem">
        @foreach($upcoming as $item)
        @php
            $y    = $item['yahrtzeit'];
            $date = $item['gregorian_date'];
            $diff = (int)\Carbon\Carbon::today()->diffInDays($date, false);
        @endphp
        <div style="flex:1 1 300px;display:flex;align-items:center;justify-content:space-between;
                    gap:0.6rem;padding:0.4rem 0.75rem;border-radius:6px;
                    background:var(--bg-subtle,#f9f7f4);border:1px solid var(--border-dim)">
            <div style="min-width:0">
                <div style="font-weight:600;font-size:0.9rem;color:var(--navy);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $y->full_name }}@if($y->hebrew_name) <span style="font-weight:400;font-size:0.78rem;direction:rtl;unicode-bidi:embed" title="{{ $y->hebrew_name }}">{{ $y->hebrew_name }}</span>@endif</div>
                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.05rem">
                    @forelse($y->families as $fam)
                        <a href="{{ route('admin.members.edit', $fam->id) }}" class="text-link">{{ $fam->name }}</a>@if(!$loop->last), @endif
                    @empty
                        &mdash;
                    @endforelse
                    @if($item['hebrew_label'])
                        &middot; <span style="direction:rtl;unicode-bidi:embed">{{ $item['hebrew_label'] }}</span>
                    @endif
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0">
                <div style="font-weight:600;font-size:0.85rem;color:var(--navy)">{{ $date->format('D, M j') }}</div>
                <div style="margin-top:0.15rem">
                    @if($diff === 0)
                        <span style="background:#fef3c7;color:#92400e;padding:0.1rem 0.5rem;border-radius:999px;font-size:0.72rem;font-weight:700">Today</span>
                    @elseif($diff === 1)
                        <span style="background:#fde8e8;color:#9b1c1c;padding:0.1rem 0.5rem;border-radius:999px;font-size:0.72rem;font-weight:600">Tomorrow</span>
                    @else
                        <span style="color:var(--text-muted);font-size:0.75rem">in {{ $diff }} days</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Add Yahrtzeit form --}}
<div id="add-form" class="card hidden" style="margin-bottom:1.25rem">
    <div class="card-title">Add Yahrtzeit</div>
    <form method="POST" action="{{ route('admin.yahrtzeits.store-global') }}">
        @csrf
        <div class="grid-2" style="margin-bottom:1rem">
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Families <span style="color:var(--gold)">*</span></label>
                <select name="family_ids[]" id="add-family-select" class="form-control" multiple required>
                    @foreach($families as $f)
                    <option value="{{ $f->id }}" {{ in_array($f->id, (array) old('family_ids', [])) ? 'selected' : '' }}>{{ $f->name }}</option>
                    @endforeach
                </select>
                <div class="text-muted text-sm" style="margin-top:0.25rem">Type to search. Select one or more families.</div>
            </div>
            <div class="form-group">
                <label class="form-label">Full Name <span style="color:var(--gold)">*</span></label>
                <input type="text" name="full_name" class="form-control" required value="{{ old('full_name') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Hebrew Name</label>
                <input type="text" name="hebrew_name" class="form-control heb-input"
                       style="direction:rtl;font-family:serif" placeholder="שם עברי"
                       value="{{ old('hebrew_name') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Relationship</label>
                <select name="relationship" class="form-control">
                    <option value="">— None —</option>
                    @foreach(['mother','father','sister','brother','child','spouse'] as $rel)
                    <option value="{{ $rel }}" {{ old('relationship') === $rel ? 'selected' : '' }}>{{ ucfirst($rel) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
            </div>
        </div>

        <div style="padding-top:1rem;border-top:1px solid var(--border-dim);margin-bottom:1rem">
            <div style="font-size:0.85rem;font-weight:600;color:var(--gold);margin-bottom:0.75rem">Date of Death</div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Gregorian Date <span class="text-muted text-sm">(auto-computes Hebrew)</span></label>
                    <input type="date" name="date_of_death" id="add-dod-greg" class="form-control" value="{{ old('date_of_death') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Hebrew Date <span class="text-muted text-sm">(auto-filled)</span></label>
                    <input type="text" name="hebrew_date_of_death" class="form-control" placeholder="e.g. 15 Tishrei 5785" value="{{ old('hebrew_date_of_death') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex;align-items:center;gap:0.4rem">
                        <input type="checkbox" name="hebrew_dod_override" value="1" {{ old('hebrew_dod_override') ? 'checked' : '' }}>
                        Lock Hebrew date (override auto-compute)
                    </label>
                </div>
            </div>
        </div>

        <div style="padding-top:1rem;border-top:1px solid var(--border-dim);margin-bottom:1rem">
            <div style="font-size:0.85rem;font-weight:600;color:var(--gold);margin-bottom:0.5rem">Annual Yahrtzeit Date <span class="text-muted" style="font-weight:400;font-size:0.75rem">(auto-filled from Gregorian date)</span></div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Hebrew Month</label>
                    <select name="hebrew_month" id="add-heb-month" class="form-control">
                        <option value="">— Select Month —</option>
                        @foreach([1=>'Tishrei',2=>'Cheshvan',3=>'Kislev',4=>'Tevet',5=>'Shevat',6=>'Adar I',7=>'Adar / Adar II',8=>'Nisan',9=>'Iyar',10=>'Sivan',11=>'Tammuz',12=>'Av',13=>'Elul'] as $num => $name)
                        <option value="{{ $num }}" {{ old('hebrew_month') == $num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Hebrew Day</label>
                    <select name="hebrew_day" id="add-heb-day" class="form-control">
                        <option value="">— Select Day —</option>
                        @for($d = 1; $d <= 30; $d++)
                        <option value="{{ $d }}" {{ old('hebrew_day') == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:0.75rem">
            <button type="submit" class="btn btn-gold">Save Yahrtzeit</button>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('add-form').classList.add('hidden')">Cancel</button>
        </div>
    </form>
</div>

{{-- Search / Filter --}}
<form method="GET" action="{{ route('admin.yahrtzeits.index') }}" style="margin-bottom:1.25rem">
    <div style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end">
        <div style="flex:2;min-width:200px">
            <input type="text" name="search" class="form-control" placeholder="Search name, Hebrew name, family..." value="{{ request('search') }}">
        </div>
        <div style="min-width:180px">
            <select name="has_dod" class="form-control">
                <option value="">All records</option>
                <option value="yes" {{ request('has_dod') === 'yes' ? 'selected' : '' }}>Has date of death</option>
                <option value="no"  {{ request('has_dod') === 'no'  ? 'selected' : '' }}>No date of death</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if(request()->hasAny(['search','has_dod']))
            <a href="{{ route('admin.yahrtzeits.index') }}" class="btn btn-outline">Clear</a>
        @endif
    </div>
</form>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Hebrew Name</th>
                <th>Family</th>
                <th>Relationship</th>
                <th>Date of Death</th>
                <th>Annual Yahrtzeit</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($yahrtzeits as $y)
            @php
                $months = [1=>'Tishrei',2=>'Cheshvan',3=>'Kislev',4=>'Tevet',5=>'Shevat',
                           6=>'Adar I',7=>'Adar / Adar II',8=>'Nisan',9=>'Iyar',10=>'Sivan',
                           11=>'Tammuz',12=>'Av',13=>'Elul'];
            @endphp
            <tr>
                <td style="font-weight:500">{{ $y->full_name }}</td>
                <td style="direction:rtl;font-family:serif;font-size:0.95rem">{{ $y->hebrew_name ?? '—' }}</td>
                <td>
                    @forelse($y->families as $fam)
                    <a href="{{ route('admin.members.show', $fam->id) }}" style="color:var(--gold);text-decoration:none;font-size:0.85rem">{{ $fam->name }}</a>@if(!$loop->last)<br>@endif
                    @empty
                    <span class="text-muted">—</span>
                    @endforelse
                </td>
                <td>
                    @if($y->relationship)
                    <span class="badge badge-muted">{{ $y->relationship_label }}</span>
                    @else
                    <span class="text-muted text-sm">—</span>
                    @endif
                </td>
                <td class="text-muted text-sm">{{ $y->date_of_death?->format('M j, Y') ?? '—' }}</td>
                <td class="text-sm">
                    {{ $y->hebrew_day }} {{ $months[$y->hebrew_month] ?? '' }}
                    @if($y->hebrew_date_of_death)
                    <br><span class="text-muted" style="font-size:0.75rem">{{ $y->hebrew_date_of_death }}</span>
                    @endif
                </td>
                <td>
                    @if($y->families->isNotEmpty())
                    <a href="{{ route('admin.yahrtzeits.edit', [$y->families->first()->id, $y->id]) }}" class="btn btn-outline btn-sm">Edit</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)">No yahrtzeits found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination">{{ $yahrtzeits->links() }}</div>
</div>

@if(session('errors') || $errors->any())
<script>document.getElementById('add-form').classList.remove('hidden');</script>
@endif
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.min.css" rel="stylesheet">
<style>
.hidden { display: none !important; }
.ts-wrapper { font-size: 0.875rem; }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new TomSelect('#add-family-select', {
        plugins: ['remove_button'],
        placeholder: 'Type to search families…',
        maxOptions: 200,
    });
});

// Auto-fill Hebrew month/day from Gregorian date (same as edit page)
document.getElementById('add-dod-greg')?.addEventListener('change', function () {
    const date = this.value;
    if (!date) return;
    fetch('{{ route('admin.yahrtzeits.hebrew-date') }}?date=' + date)
        .then(r => r.json())
        .then(data => {
            if (data.month) {
                document.getElementById('add-heb-month').value = data.month;
                document.getElementById('add-heb-day').value   = data.day;
            }
        });
});
</script>
@include('admin.yahrtzeits._hebrew_keyboard')
@endsection
