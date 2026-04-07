@extends('layouts.admin')

@section('title', 'Minyanim')

@section('styles')
<style>
/* ── Base table styles ── */
.minyan-row td { vertical-align: middle; }
.minyan-row.editing td { background: rgba(201,168,76,0.04); }
.minyan-row input[type="text"], .minyan-row input[type="number"], .minyan-row select {
    background: var(--bg); border: 1px solid var(--border-dim); border-radius:6px;
    color: var(--text); padding: 0.3rem 0.5rem; font-size: 0.8rem; width: 100%;
}
.minyan-row input:focus, .minyan-row select:focus { outline:none; border-color:var(--gold); }
.drag-handle { cursor: grab; color: var(--text-muted); padding: 0 0.4rem; user-select:none; }
.drag-handle:active { cursor: grabbing; }
#sortable-body tr { transition: background 0.1s; }
#sortable-body tr.dragging { opacity: 0.5; }
.type-badge.shacharis { background:rgba(52,152,219,0.15);color:#7ec8f5; }
.type-badge.mincha    { background:rgba(243,156,18,0.15); color:#f5c76b; }
.type-badge.maariv    { background:rgba(155,89,182,0.15); color:#c39bd3; }
.type-badge.other     { background:rgba(255,255,255,0.08);color:var(--text-muted); }

/* ── Detail panel (accordion) ── */
.detail-row td { padding: 0; background: var(--bg-card2); }
.detail-panel  { padding: 1.25rem 1.5rem; border-top: 1px solid var(--border-dim); }
.detail-panel h4 { color: var(--gold); font-size:0.85rem; letter-spacing:.05em; text-transform:uppercase; margin-bottom:.75rem; }

/* ── Time rule grid ── */
.time-rule-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:.5rem; margin-bottom:1rem; }
.time-rule-cell { background:var(--bg); border:1px solid var(--border-dim); border-radius:8px; padding:.6rem; font-size:.75rem; }
.time-rule-cell label { display:block; font-size:.65rem; color:var(--text-muted); margin-bottom:.3rem; font-weight:600; }
.time-rule-cell select, .time-rule-cell input[type="text"], .time-rule-cell input[type="number"] {
    background: var(--bg-card); border:1px solid var(--border-dim); border-radius:4px;
    color:var(--text); padding:.25rem .4rem; font-size:.75rem; width:100%; margin-bottom:.25rem;
}
.time-rule-cell select:focus, .time-rule-cell input:focus { outline:none; border-color:var(--gold); }
.mode-static  .dynamic-fields  { display:none; }
.mode-dynamic .static-fields   { display:none; }
.mode-clear   .dynamic-fields,
.mode-clear   .static-fields   { display:none; }

/* ── Exceptions table ── */
.exceptions-table { width:100%; border-collapse:collapse; font-size:.8rem; margin-bottom:1rem; }
.exceptions-table th { color:var(--text-muted); font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; padding:.4rem .6rem; border-bottom:1px solid var(--border-dim); text-align:left; }
.exceptions-table td { padding:.45rem .6rem; border-bottom:1px solid rgba(255,255,255,0.04); vertical-align:middle; }
.exceptions-table tr:last-child td { border-bottom:none; }

/* ── Add Exception form ── */
.exc-form { background:var(--bg); border:1px solid var(--border-dim); border-radius:8px; padding:1rem; margin-top:.75rem; display:none; }
.exc-form .form-row { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:.6rem; margin-bottom:.6rem; }
.exc-form label { font-size:.7rem; color:var(--text-muted); display:block; margin-bottom:.2rem; }
.exc-form select, .exc-form input[type="text"], .exc-form input[type="number"] {
    background:var(--bg-card); border:1px solid var(--border-dim); border-radius:5px;
    color:var(--text); padding:.3rem .5rem; font-size:.8rem; width:100%;
}
.exc-form select:focus, .exc-form input:focus { outline:none; border-color:var(--gold); }
.exc-form .override-extra { margin-top:.5rem; }
.exc-form .override-fields { display:none; }
.exc-form .override-fields.active { display:block; }

/* ── Feedback ── */
.feedback { font-size:.78rem; margin-top:.4rem; padding:.3rem .6rem; border-radius:4px; }
.feedback.ok  { background:rgba(46,204,113,.15); color:#2ecc71; }
.feedback.err { background:rgba(231,76,60,.15);  color:#e74c3c; }
</style>
@endsection

@section('content')
<div class="flex items-center justify-between" style="margin-bottom:1.5rem">
    <div>
        <h1 class="page-title">Minyanim Schedule</h1>
        <p class="page-subtitle">Drag to reorder. Click "Edit" to modify base schedule and holiday exceptions.</p>
    </div>
    <div style="display:flex;gap:.75rem">
        <a href="{{ route('admin.calendar.settings') }}" class="btn btn-outline">Settings</a>
        <a href="{{ route('admin.calendar.generate') }}" class="btn btn-gold">Generate Calendar</a>
    </div>
</div>

<div class="card" style="overflow-x:auto">
    <table class="table" id="minyanim-table">
        <thead>
            <tr>
                <th style="width:28px"></th>
                <th>Name</th>
                <th>Type</th>
                <th>Sun</th>
                <th>Mon</th>
                <th>Tue</th>
                <th>Wed</th>
                <th>Thu</th>
                <th>Fri</th>
                <th>Sat</th>
                <th>Active</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="sortable-body">
        @foreach($minyanim as $m)
        {{-- View row --}}
        <tr class="minyan-row" data-id="{{ $m->id }}" id="row-{{ $m->id }}">
            <td><span class="drag-handle" title="Drag to reorder">&#9776;</span></td>
            <td class="cell-view name-view">{{ $m->name }}</td>
            <td class="cell-view type-view">
                <span class="badge type-badge {{ $m->type }}">{{ ucfirst($m->type) }}</span>
            </td>
            @foreach(['sun','mon','tue','wed','thu','fri','sat'] as $d)
            <td class="cell-view {{ $d }}-view">{{ $m->{$d} ?: '—' }}</td>
            @endforeach
            <td class="cell-view active-view">
                @if($m->active)
                    <span class="badge badge-green">Yes</span>
                @else
                    <span class="badge badge-muted">No</span>
                @endif
            </td>
            <td class="cell-view notes-view" style="max-width:150px;font-size:0.75rem;color:var(--text-muted)">
                {{ $m->notes }}
            </td>
            <td>
                <button class="btn btn-outline btn-sm" onclick="editRow({{ $m->id }})">Edit</button>
                <button class="btn btn-outline btn-sm" onclick="toggleDetail({{ $m->id }})">Detail</button>
                <form method="POST" action="{{ route('admin.calendar.minyanim.delete', $m->id) }}"
                      style="display:inline" onsubmit="return confirm('Delete this minyan?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Del</button>
                </form>
            </td>
        </tr>

        {{-- Inline edit row (hidden) --}}
        <tr class="minyan-row editing" id="edit-row-{{ $m->id }}" style="display:none">
            <td></td>
            <td><input type="text" id="edit-name-{{ $m->id }}" value="{{ $m->name }}"></td>
            <td>
                <select id="edit-type-{{ $m->id }}">
                    @foreach(['shacharis','mincha','maariv','other'] as $t)
                    <option value="{{ $t }}" {{ $m->type === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </td>
            @foreach(['sun','mon','tue','wed','thu','fri','sat'] as $d)
            <td><input type="text" id="edit-{{ $d }}-{{ $m->id }}" value="{{ $m->{$d} }}" placeholder="—" style="width:60px"></td>
            @endforeach
            <td>
                <select id="edit-active-{{ $m->id }}">
                    <option value="1" {{ $m->active ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !$m->active ? 'selected' : '' }}>No</option>
                </select>
            </td>
            <td><input type="text" id="edit-notes-{{ $m->id }}" value="{{ $m->notes }}"></td>
            <td>
                <button class="btn btn-gold btn-sm" onclick="saveRow({{ $m->id }})">Save</button>
                <button class="btn btn-outline btn-sm" onclick="cancelEdit({{ $m->id }})">Cancel</button>
            </td>
        </tr>

        {{-- Detail panel row (hidden) --}}
        <tr id="detail-row-{{ $m->id }}" style="display:none">
            <td colspan="13">
                <div class="detail-panel">

                    {{-- ── A) Base Schedule (time rules per DOW) ── --}}
                    <h4>A) Base Schedule — Per-Day Time Rules</h4>
                    <p style="font-size:.75rem;color:var(--text-muted);margin-bottom:.75rem">
                        Set "Static" to use a fixed time, "Dynamic" to compute from sun events, or "Clear" for no minyan that day.
                        These override the simple time columns above when saved.
                    </p>

                    <div class="time-rule-grid" id="time-rule-grid-{{ $m->id }}" data-minyan-id="{{ $m->id }}"
                         data-rules="{{ json_encode($m->time_rules ?? new stdClass) }}">
                        @foreach(['sun','mon','tue','wed','thu','fri','sat'] as $idx => $d)
                        <div class="time-rule-cell" id="trc-{{ $m->id }}-{{ $d }}">
                            <label>{{ strtoupper($d) }}</label>
                            <select onchange="onModeChange({{ $m->id }}, '{{ $d }}', this.value)" id="trmode-{{ $m->id }}-{{ $d }}">
                                <option value="static">Static</option>
                                <option value="dynamic">Dynamic</option>
                                <option value="clear">Clear</option>
                            </select>
                            <div class="static-fields">
                                <input type="text" placeholder="e.g. 6:35" id="trtime-{{ $m->id }}-{{ $d }}" style="margin-top:.2rem">
                            </div>
                            <div class="dynamic-fields">
                                <select id="trref-{{ $m->id }}-{{ $d }}">
                                    <option value="sunset">Sunset</option>
                                    <option value="sunrise">Sunrise</option>
                                    <option value="alos">Alos</option>
                                    <option value="hour3">3rd Hour</option>
                                </select>
                                <input type="number" placeholder="Offset min" id="troffset-{{ $m->id }}-{{ $d }}" style="margin-top:.2rem">
                                <select id="trround-{{ $m->id }}-{{ $d }}" style="margin-top:.2rem">
                                    <option value="nearest5">Nearest 5 min</option>
                                    <option value="nearest10">Nearest 10 min</option>
                                    <option value="floor5">Floor 5 min</option>
                                    <option value="ceiling5">Ceiling 5 min</option>
                                    <option value="none">No rounding</option>
                                </select>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <button class="btn btn-gold btn-sm" onclick="saveTimeRules({{ $m->id }})">Save Base Schedule</button>
                    <span id="tr-feedback-{{ $m->id }}" class="feedback" style="display:none"></span>

                    <hr style="border-color:var(--border-dim);margin:1.25rem 0">

                    {{-- ── B) Holiday Exceptions ── --}}
                    <h4>B) Holiday Exceptions</h4>
                    <p style="font-size:.75rem;color:var(--text-muted);margin-bottom:.75rem">
                        Override the computed time for this minyan on specific holiday types.
                        Higher priority wins when multiple rules match.
                    </p>

                    <div id="exc-list-{{ $m->id }}">
                        <p style="color:var(--text-muted);font-size:.8rem">Loading…</p>
                    </div>

                    <button class="btn btn-outline btn-sm" onclick="toggleAddExcForm({{ $m->id }})">+ Add Exception</button>

                    {{-- Add Exception form --}}
                    <div class="exc-form" id="exc-form-{{ $m->id }}">
                        <div class="form-row">
                            <div>
                                <label>Event Type</label>
                                <select id="exc-event-{{ $m->id }}" onchange="refreshExcOverrideFields({{ $m->id }})">
                                    <option value="rosh_hashana">Rosh Hashana</option>
                                    <option value="yom_kippur">Yom Kippur</option>
                                    <option value="erev_yom_kippur">Erev Yom Kippur</option>
                                    <option value="yom_tov">Major Yom Tov (Sukkot/Pesach/Shavuot)</option>
                                    <option value="erev_yom_tov">Erev Yom Tov</option>
                                    <option value="chol_hamoed">Chol HaMoed</option>
                                    <option value="hoshana_raba">Hoshana Raba</option>
                                    <option value="rosh_chodesh">Rosh Chodesh</option>
                                    <option value="chanukah">Chanukah</option>
                                    <option value="fast_minor">Minor Fast Day</option>
                                    <option value="tisha_bav">Tisha B'Av</option>
                                    <option value="purim">Purim</option>
                                    <option value="selichos">Selichot Period</option>
                                    <option value="civil_holiday">Civil/Legal Holiday</option>
                                </select>
                            </div>
                            <div>
                                <label>Day Type</label>
                                <select id="exc-daytype-{{ $m->id }}">
                                    <option value="any">Any Day</option>
                                    <option value="weekday">Weekday (Mon–Fri)</option>
                                    <option value="sunday">Sunday</option>
                                    <option value="shabbos">Shabbat</option>
                                </select>
                            </div>
                            <div>
                                <label>Override Type</label>
                                <select id="exc-ovtype-{{ $m->id }}" onchange="refreshExcOverrideFields({{ $m->id }})">
                                    <option value="static">Static Time</option>
                                    <option value="dynamic">Dynamic (sun offset)</option>
                                    <option value="relative">Relative Offset</option>
                                    <option value="prepend">Prepend Time</option>
                                    <option value="hidden">Hide Minyan</option>
                                </select>
                            </div>
                            <div>
                                <label>Priority (1–100)</label>
                                <input type="number" id="exc-priority-{{ $m->id }}" value="10" min="1" max="100">
                            </div>
                            <div>
                                <label>Notes</label>
                                <input type="text" id="exc-notes-{{ $m->id }}" placeholder="Optional">
                            </div>
                        </div>

                        {{-- Override value sub-fields --}}
                        <div class="override-extra">
                            {{-- Static --}}
                            <div class="override-fields active" id="exc-ov-static-{{ $m->id }}">
                                <label style="font-size:.7rem;color:var(--text-muted)">Time</label>
                                <input type="text" id="exc-ov-static-time-{{ $m->id }}" placeholder="e.g. 6:20" style="max-width:120px">
                            </div>
                            {{-- Dynamic --}}
                            <div class="override-fields" id="exc-ov-dynamic-{{ $m->id }}">
                                <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end">
                                    <div>
                                        <label style="font-size:.7rem;color:var(--text-muted)">Ref</label>
                                        <select id="exc-ov-dyn-ref-{{ $m->id }}" style="min-width:100px">
                                            <option value="sunset">Sunset</option>
                                            <option value="sunrise">Sunrise</option>
                                            <option value="alos">Alos</option>
                                            <option value="hour3">3rd Hour</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label style="font-size:.7rem;color:var(--text-muted)">Offset (min)</label>
                                        <input type="number" id="exc-ov-dyn-offset-{{ $m->id }}" placeholder="-13" style="max-width:80px">
                                    </div>
                                    <div>
                                        <label style="font-size:.7rem;color:var(--text-muted)">Round</label>
                                        <select id="exc-ov-dyn-round-{{ $m->id }}" style="min-width:130px">
                                            <option value="nearest5">Nearest 5 min</option>
                                            <option value="nearest10">Nearest 10 min</option>
                                            <option value="floor5">Floor 5 min</option>
                                            <option value="ceiling5">Ceiling 5 min</option>
                                            <option value="none">No rounding</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            {{-- Relative --}}
                            <div class="override-fields" id="exc-ov-relative-{{ $m->id }}">
                                <label style="font-size:.7rem;color:var(--text-muted)">Offset (min, negative = earlier)</label>
                                <input type="number" id="exc-ov-rel-offset-{{ $m->id }}" placeholder="-10" style="max-width:100px">
                            </div>
                            {{-- Prepend --}}
                            <div class="override-fields" id="exc-ov-prepend-{{ $m->id }}">
                                <label style="font-size:.7rem;color:var(--text-muted)">Prepend Time</label>
                                <input type="text" id="exc-ov-pre-time-{{ $m->id }}" placeholder="e.g. 6:20" style="max-width:120px">
                            </div>
                            {{-- Hidden — no extra fields --}}
                        </div>

                        <div style="margin-top:.75rem;display:flex;gap:.5rem;align-items:center">
                            <button class="btn btn-gold btn-sm" onclick="saveException({{ $m->id }}, null)">Save Exception</button>
                            <button class="btn btn-outline btn-sm" onclick="toggleAddExcForm({{ $m->id }})">Cancel</button>
                            <span id="exc-feedback-{{ $m->id }}" class="feedback" style="display:none"></span>
                        </div>
                    </div>

                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

{{-- Add Minyan --}}
<div class="card" style="margin-top:1.5rem">
    <div class="card-title">Add Minyan</div>
    <form method="POST" action="{{ route('admin.calendar.minyanim.store') }}">
        @csrf
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Shacharis" required>
            </div>
            <div class="form-group">
                <label class="form-label">Type</label>
                <select name="type" class="form-control">
                    <option value="shacharis">Shacharis</option>
                    <option value="mincha">Mincha</option>
                    <option value="maariv">Maariv</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="{{ $minyanim->count() + 1 }}">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:.75rem;margin-bottom:1rem">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
            <div class="form-group" style="margin:0">
                <label class="form-label" style="font-size:0.65rem">{{ $d }}</label>
                <input type="text" name="{{ strtolower($d) }}" class="form-control" placeholder="—">
            </div>
            @endforeach
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Active</label>
                <select name="active" class="form-control">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <input type="text" name="notes" class="form-control" placeholder="Optional notes">
            </div>
        </div>
        <button type="submit" class="btn btn-gold">Add Minyan</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
// ─────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────
const CSRF = document.querySelector('meta[name=csrf-token]')?.content
    || '{{ csrf_token() }}';

const EVENT_TYPE_LABELS = {
    rosh_hashana:   'Rosh Hashana',
    yom_kippur:     'Yom Kippur',
    erev_yom_kippur:'Erev Yom Kippur',
    yom_tov:        'Major Yom Tov (Sukkot/Pesach/Shavuot)',
    erev_yom_tov:   'Erev Yom Tov',
    chol_hamoed:    'Chol HaMoed',
    hoshana_raba:   'Hoshana Raba',
    rosh_chodesh:   'Rosh Chodesh',
    chanukah:       'Chanukah',
    fast_minor:     'Minor Fast Day',
    tisha_bav:      "Tisha B'Av",
    purim:          'Purim',
    selichos:       'Selichot Period',
    civil_holiday:  'Civil/Legal Holiday',
};

function showFeedback(id, ok, msg) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = '';
    el.className = 'feedback ' + (ok ? 'ok' : 'err');
    el.textContent = msg;
    setTimeout(() => { el.style.display = 'none'; }, 3500);
}

// ─────────────────────────────────────────────
// Inline edit (existing behaviour preserved)
// ─────────────────────────────────────────────
function editRow(id) {
    document.getElementById('row-' + id).style.display = 'none';
    document.getElementById('edit-row-' + id).style.display = '';
}
function cancelEdit(id) {
    document.getElementById('edit-row-' + id).style.display = 'none';
    document.getElementById('row-' + id).style.display = '';
}
function saveRow(id) {
    const data = {
        _method: 'PUT',
        _token:  CSRF,
        name:    document.getElementById('edit-name-'   + id).value,
        type:    document.getElementById('edit-type-'   + id).value,
        sun:     document.getElementById('edit-sun-'    + id).value,
        mon:     document.getElementById('edit-mon-'    + id).value,
        tue:     document.getElementById('edit-tue-'    + id).value,
        wed:     document.getElementById('edit-wed-'    + id).value,
        thu:     document.getElementById('edit-thu-'    + id).value,
        fri:     document.getElementById('edit-fri-'    + id).value,
        sat:     document.getElementById('edit-sat-'    + id).value,
        active:  document.getElementById('edit-active-' + id).value,
        notes:   document.getElementById('edit-notes-'  + id).value,
    };
    fetch('{{ url('admin/calendar/minyanim') }}/' + id, {
        method:  'POST',
        headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body:    JSON.stringify(data),
    })
    .then(r => r.json())
    .then(resp => { if (resp.success) location.reload(); else alert('Error saving.'); })
    .catch(() => alert('Error saving.'));
}

// ─────────────────────────────────────────────
// Detail panel toggle
// ─────────────────────────────────────────────
const detailLoaded = {};
function toggleDetail(id) {
    const row = document.getElementById('detail-row-' + id);
    const shown = row.style.display !== 'none';
    row.style.display = shown ? 'none' : '';
    if (!shown && !detailLoaded[id]) {
        detailLoaded[id] = true;
        initTimeRuleGrid(id);
        loadExceptions(id);
    }
}

// ─────────────────────────────────────────────
// A) Time Rule Grid
// ─────────────────────────────────────────────
const DAYS = ['sun','mon','tue','wed','thu','fri','sat'];

function parseRoundValue(v) {
    if (!v || v === 'none') return {round_to: 0, round_dir: 'nearest'};
    const map = {
        nearest5:  {round_to:5,  round_dir:'nearest'},
        nearest10: {round_to:10, round_dir:'nearest'},
        floor5:    {round_to:5,  round_dir:'floor'},
        ceiling5:  {round_to:5,  round_dir:'ceiling'},
    };
    return map[v] || {round_to:0, round_dir:'nearest'};
}

function roundingToSelect(round_to, round_dir) {
    if (!round_to) return 'none';
    if (round_to === 5  && round_dir === 'nearest') return 'nearest5';
    if (round_to === 10 && round_dir === 'nearest') return 'nearest10';
    if (round_to === 5  && round_dir === 'floor')   return 'floor5';
    if (round_to === 5  && round_dir === 'ceiling') return 'ceiling5';
    return 'none';
}

function onModeChange(id, day, mode) {
    const cell = document.getElementById('trc-' + id + '-' + day);
    cell.classList.remove('mode-static','mode-dynamic','mode-clear');
    cell.classList.add('mode-' + mode);
}

function initTimeRuleGrid(id) {
    const grid = document.getElementById('time-rule-grid-' + id);
    let rules  = {};
    try { rules = JSON.parse(grid.dataset.rules || '{}'); } catch(e) {}

    DAYS.forEach(d => {
        const rule = rules[d] || null;
        const mode = rule ? rule.type || 'static' : 'static';
        const selMode = document.getElementById('trmode-' + id + '-' + d);
        if (!selMode) return;
        selMode.value = (mode === 'hidden' || mode === 'clear') ? 'clear' : mode;
        onModeChange(id, d, selMode.value);

        if (mode === 'static') {
            const inp = document.getElementById('trtime-' + id + '-' + d);
            if (inp) inp.value = rule ? (rule.time || '') : '';
        } else if (mode === 'dynamic') {
            const ref   = document.getElementById('trref-'    + id + '-' + d);
            const off   = document.getElementById('troffset-' + id + '-' + d);
            const round = document.getElementById('trround-'  + id + '-' + d);
            if (ref)   ref.value   = rule.ref        || 'sunset';
            if (off)   off.value   = rule.offset_min != null ? rule.offset_min : '';
            if (round) round.value = roundingToSelect(rule.round_to, rule.round_dir);
        }
    });
}

function saveTimeRules(id) {
    const rules = {};
    DAYS.forEach(d => {
        const selMode = document.getElementById('trmode-' + id + '-' + d);
        if (!selMode) return;
        const mode = selMode.value;
        if (mode === 'clear') {
            rules[d] = {type: 'static', time: null};
        } else if (mode === 'static') {
            const time = (document.getElementById('trtime-' + id + '-' + d)?.value || '').trim();
            rules[d] = {type: 'static', time: time || null};
        } else if (mode === 'dynamic') {
            const ref    = document.getElementById('trref-'    + id + '-' + d)?.value || 'sunset';
            const offset = parseInt(document.getElementById('troffset-' + id + '-' + d)?.value || '0', 10);
            const rv     = parseRoundValue(document.getElementById('trround-' + id + '-' + d)?.value);
            rules[d] = {type: 'dynamic', ref, offset_min: offset, ...rv};
        }
    });

    fetch('{{ url('admin/calendar/minyanim') }}/' + id + '/time-rules', {
        method:  'POST',
        headers: {
            'Content-Type':     'application/json',
            'X-CSRF-TOKEN':     CSRF,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({time_rules: rules}),
    })
    .then(r => r.json())
    .then(resp => {
        if (resp.success) {
            showFeedback('tr-feedback-' + id, true, 'Saved!');
        } else {
            showFeedback('tr-feedback-' + id, false, 'Error saving.');
        }
    })
    .catch(() => showFeedback('tr-feedback-' + id, false, 'Network error.'));
}

// ─────────────────────────────────────────────
// B) Exceptions
// ─────────────────────────────────────────────
function loadExceptions(id) {
    fetch('{{ url('admin/calendar/minyanim') }}/' + id + '/exceptions', {
        headers: {'X-Requested-With': 'XMLHttpRequest'},
    })
    .then(r => r.json())
    .then(resp => {
        if (resp.success) renderExceptions(id, resp.exceptions);
    })
    .catch(() => {
        document.getElementById('exc-list-' + id).innerHTML =
            '<p style="color:var(--error);font-size:.8rem">Failed to load exceptions.</p>';
    });
}

function renderExceptions(id, exceptions) {
    const container = document.getElementById('exc-list-' + id);
    if (!exceptions || exceptions.length === 0) {
        container.innerHTML = '<p style="color:var(--text-muted);font-size:.8rem;margin-bottom:.5rem">No exceptions defined.</p>';
        return;
    }

    const overrideLabel = (ex) => {
        switch(ex.override_type) {
            case 'static':   return 'Static: ' + (ex.override_value?.time || '—');
            case 'dynamic': {
                const ov = ex.override_value || {};
                return 'Dynamic: ' + (ov.ref||'sunset') + ' ' + (ov.offset_min >= 0 ? '+' : '') + (ov.offset_min||0) + 'min';
            }
            case 'relative': return 'Relative: ' + ((ex.override_value?.offset_min||0) >= 0 ? '+' : '') + (ex.override_value?.offset_min||0) + ' min';
            case 'prepend':  return 'Prepend: ' + (ex.override_value?.time || '—');
            case 'hidden':   return 'Hidden';
            default:         return ex.override_type;
        }
    };

    let html = `<table class="exceptions-table">
        <thead><tr>
            <th>Event Type</th><th>Day Type</th><th>Override</th><th>Priority</th><th>Notes</th><th>Actions</th>
        </tr></thead><tbody>`;

    exceptions.forEach(ex => {
        html += `<tr data-exc-id="${ex.id}">
            <td>${EVENT_TYPE_LABELS[ex.event_type] || ex.event_type}</td>
            <td>${ex.day_type}</td>
            <td>${overrideLabel(ex)}</td>
            <td>${ex.priority}</td>
            <td style="color:var(--text-muted)">${ex.notes || ''}</td>
            <td>
                <button class="btn btn-outline btn-sm" onclick="editException(${id}, ${ex.id})">Edit</button>
                <button class="btn btn-danger btn-sm" onclick="deleteException(${id}, ${ex.id})">Del</button>
            </td>
        </tr>`;
    });

    html += '</tbody></table>';
    container.innerHTML = html;
}

function toggleAddExcForm(id) {
    const form = document.getElementById('exc-form-' + id);
    form.style.display = form.style.display === 'none' || !form.style.display ? '' : 'none';
    // reset editing state
    form.dataset.editingId = '';
    // reset save button label
    const saveBtn = form.querySelector('button[onclick*="saveException"]');
    if (saveBtn) saveBtn.textContent = 'Save Exception';
    refreshExcOverrideFields(id);
}

function refreshExcOverrideFields(id) {
    const ovtype = document.getElementById('exc-ovtype-' + id)?.value;
    ['static','dynamic','relative','prepend'].forEach(t => {
        const el = document.getElementById('exc-ov-' + t + '-' + id);
        if (el) el.classList.remove('active');
    });
    const target = document.getElementById('exc-ov-' + ovtype + '-' + id);
    if (target) target.classList.add('active');
}

function buildOverrideValue(id) {
    const ovtype = document.getElementById('exc-ovtype-' + id)?.value;
    if (ovtype === 'hidden')   return null;
    if (ovtype === 'static')   return { time: document.getElementById('exc-ov-static-time-' + id)?.value || null };
    if (ovtype === 'relative') return { offset_min: parseInt(document.getElementById('exc-ov-rel-offset-' + id)?.value || '0', 10) };
    if (ovtype === 'prepend')  return { time: document.getElementById('exc-ov-pre-time-' + id)?.value || null };
    if (ovtype === 'dynamic') {
        const rv = parseRoundValue(document.getElementById('exc-ov-dyn-round-' + id)?.value);
        return {
            ref:        document.getElementById('exc-ov-dyn-ref-'    + id)?.value || 'sunset',
            offset_min: parseInt(document.getElementById('exc-ov-dyn-offset-' + id)?.value || '0', 10),
            ...rv,
        };
    }
    return null;
}

function fillExcForm(id, ex) {
    const form = document.getElementById('exc-form-' + id);
    form.style.display = '';
    form.dataset.editingId = ex.id;

    document.getElementById('exc-event-'    + id).value = ex.event_type;
    document.getElementById('exc-daytype-'  + id).value = ex.day_type;
    document.getElementById('exc-ovtype-'   + id).value = ex.override_type;
    document.getElementById('exc-priority-' + id).value = ex.priority;
    document.getElementById('exc-notes-'    + id).value = ex.notes || '';

    const ov = ex.override_value || {};
    if (ex.override_type === 'static') {
        const el = document.getElementById('exc-ov-static-time-' + id);
        if (el) el.value = ov.time || '';
    } else if (ex.override_type === 'dynamic') {
        const ref   = document.getElementById('exc-ov-dyn-ref-'    + id);
        const off   = document.getElementById('exc-ov-dyn-offset-' + id);
        const round = document.getElementById('exc-ov-dyn-round-'  + id);
        if (ref)   ref.value   = ov.ref        || 'sunset';
        if (off)   off.value   = ov.offset_min != null ? ov.offset_min : '';
        if (round) round.value = roundingToSelect(ov.round_to, ov.round_dir);
    } else if (ex.override_type === 'relative') {
        const el = document.getElementById('exc-ov-rel-offset-' + id);
        if (el) el.value = ov.offset_min != null ? ov.offset_min : '';
    } else if (ex.override_type === 'prepend') {
        const el = document.getElementById('exc-ov-pre-time-' + id);
        if (el) el.value = ov.time || '';
    }

    refreshExcOverrideFields(id);
    const saveBtn = form.querySelector('button[onclick*="saveException"]');
    if (saveBtn) saveBtn.textContent = 'Update Exception';
}

function editException(minyanId, exId) {
    // Fetch fresh data then fill form
    fetch('{{ url('admin/calendar/minyanim') }}/' + minyanId + '/exceptions', {
        headers: {'X-Requested-With': 'XMLHttpRequest'},
    })
    .then(r => r.json())
    .then(resp => {
        if (resp.success) {
            const ex = resp.exceptions.find(e => e.id == exId);
            if (ex) fillExcForm(minyanId, ex);
        }
    });
}

function saveException(minyanId, _ignored) {
    const form      = document.getElementById('exc-form-' + minyanId);
    const editingId = form.dataset.editingId;
    const payload   = {
        event_type:     document.getElementById('exc-event-'    + minyanId)?.value,
        day_type:       document.getElementById('exc-daytype-'  + minyanId)?.value,
        override_type:  document.getElementById('exc-ovtype-'   + minyanId)?.value,
        override_value: buildOverrideValue(minyanId),
        priority:       parseInt(document.getElementById('exc-priority-' + minyanId)?.value || '10', 10),
        notes:          document.getElementById('exc-notes-'    + minyanId)?.value || null,
    };

    const isEdit = editingId && editingId !== '';
    const url    = isEdit
        ? '{{ url('admin/calendar/minyanim') }}/' + minyanId + '/exceptions/' + editingId
        : '{{ url('admin/calendar/minyanim') }}/' + minyanId + '/exceptions';

    fetch(url, {
        method:  isEdit ? 'PUT' : 'POST',
        headers: {
            'Content-Type':     'application/json',
            'X-CSRF-TOKEN':     CSRF,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(resp => {
        if (resp.success) {
            showFeedback('exc-feedback-' + minyanId, true, isEdit ? 'Updated!' : 'Saved!');
            form.style.display = 'none';
            form.dataset.editingId = '';
            loadExceptions(minyanId);
        } else {
            showFeedback('exc-feedback-' + minyanId, false, 'Validation error.');
        }
    })
    .catch(() => showFeedback('exc-feedback-' + minyanId, false, 'Network error.'));
}

function deleteException(minyanId, exId) {
    if (!confirm('Delete this exception?')) return;
    fetch('{{ url('admin/calendar/minyanim') }}/' + minyanId + '/exceptions/' + exId, {
        method:  'DELETE',
        headers: {
            'X-CSRF-TOKEN':     CSRF,
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    .then(r => r.json())
    .then(resp => {
        if (resp.success) loadExceptions(minyanId);
    })
    .catch(() => alert('Error deleting exception.'));
}

// ─────────────────────────────────────────────
// Drag-to-reorder (preserved exactly)
// ─────────────────────────────────────────────
(function () {
    const tbody = document.getElementById('sortable-body');
    let dragged = null;

    tbody.querySelectorAll('.drag-handle').forEach(handle => {
        const row = handle.closest('tr');
        row.setAttribute('draggable', true);

        row.addEventListener('dragstart', e => {
            dragged = row;
            row.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });
        row.addEventListener('dragend', () => {
            row.classList.remove('dragging');
            dragged = null;
            saveOrder();
        });
        row.addEventListener('dragover', e => {
            e.preventDefault();
            if (dragged && dragged !== row && row.dataset.id) {
                const rect = row.getBoundingClientRect();
                const next = (e.clientY - rect.top) > (rect.height / 2);
                tbody.insertBefore(dragged, next ? row.nextSibling : row);
            }
        });
    });

    function saveOrder() {
        const ids = Array.from(tbody.querySelectorAll('tr[data-id]')).map(r => r.dataset.id);
        fetch('{{ route('admin.calendar.minyanim.reorder') }}', {
            method: 'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     CSRF,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ ids }),
        });
    }
})();
</script>
@endsection
