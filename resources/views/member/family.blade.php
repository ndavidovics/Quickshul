@extends('layouts.app')
@section('title', 'My Family')

@section('content')
<h1 class="page-title">My Family</h1>
<p class="page-subtitle">Family members, Hebrew names, birthdays, and yahrzeits</p>

@if(!$family)
    <div class="card"><p class="text-muted">No family account linked. Please contact the office.</p></div>
@else

{{-- Upcoming events summary --}}
@if($yahrzeits->count() || $birthdays->count())
<div class="grid-2" style="margin-bottom:1.5rem">
    @if($yahrzeits->count())
    <div class="card">
        <div class="card-title">⭐ Upcoming Yahrzeits</div>
        @foreach($yahrzeits as $y)
        <div style="display:flex;justify-content:space-between;padding:0.45rem 0;border-bottom:1px solid var(--border-dim)">
            <span>{{ $y['member']->full_name }}</span>
            <span class="badge badge-gold">{{ $y['gregorian_date']->format('M j') }}</span>
        </div>
        @endforeach
    </div>
    @endif
    @if($birthdays->count())
    <div class="card">
        <div class="card-title">🎂 Upcoming Birthdays</div>
        @foreach($birthdays as $b)
        <div style="display:flex;justify-content:space-between;padding:0.45rem 0;border-bottom:1px solid var(--border-dim)">
            <span>{{ $b['member']->full_name }}</span>
            <span class="badge badge-blue">{{ $b['gregorian_date']->format('M j') }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endif

{{-- Contact Information --}}
<div class="card" style="margin-bottom:1.5rem">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem">
        <div class="card-title" style="margin-bottom:0">Contact Information</div>
        <button type="button" class="btn btn-outline btn-sm" onclick="toggleSection('contact-form')">Edit</button>
    </div>
    <table style="width:100%;font-size:0.875rem;border-collapse:collapse">
        @foreach([
            'Phone'   => $family->phone ?? '—',
            'Address' => trim(($family->address??'').', '.($family->city??'').($family->state?', '.$family->state:'').($family->zip?' '.$family->zip:''), ', ') ?: '—',
        ] as $label => $value)
        <tr>
            <td style="padding:0.4rem 0;color:var(--text-muted);width:30%">{{ $label }}</td>
            <td style="padding:0.4rem 0">{{ $value }}</td>
        </tr>
        @endforeach
    </table>

    <div id="contact-form" style="display:none;margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border-dim)">
        <form method="POST" action="{{ route('family.contact.update') }}">
            @csrf
            <div class="grid-2" style="gap:1rem;margin-bottom:1rem">
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $family->phone) }}">
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $family->address) }}">
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $family->city) }}">
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">State</label>
                    <input type="text" name="state" class="form-control" value="{{ old('state', $family->state) }}">
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">ZIP</label>
                    <input type="text" name="zip" class="form-control" value="{{ old('zip', $family->zip) }}">
                </div>
            </div>
            <div style="display:flex;gap:0.75rem">
                <button type="submit" class="btn btn-gold btn-sm">Save Changes</button>
                <button type="button" class="btn btn-outline btn-sm" onclick="toggleSection('contact-form')">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Members table --}}
<div class="card">
    <div class="card-title">Family Members</div>
    @if($membersWithDates->isEmpty())
        <p class="text-muted text-sm">No family members on record.</p>
    @else
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Hebrew Name</th>
                <th>Role</th>
                <th>Birthday</th>
                <th>Hebrew Birthday</th>
                <th>Yahrzeit</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($membersWithDates as $m)
            <tr>
                <td>
                    <div style="font-weight:500">{{ $m['model']->full_name }}</div>
                    @if($m['model']->isDeceased())
                        <span class="badge badge-muted" style="margin-top:0.2rem">Deceased</span>
                    @endif
                </td>
                <td style="font-family:serif;font-size:1rem;direction:rtl">{{ $m['hebrew_name'] ?? '—' }}</td>
                <td><span class="badge badge-muted">{{ ucfirst($m['role']) }}</span></td>
                <td class="text-muted">{{ $m['model']->date_of_birth?->format('M j, Y') ?? '—' }}</td>
                <td>
                    @if($m['hebrew_dob_computed'])
                        <span style="font-size:0.85rem">{{ $m['hebrew_dob_computed']['formatted'] }}</span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if($m['model']->isDeceased() && $m['hebrew_dod_computed'])
                        <span style="font-size:0.85rem">{{ $m['hebrew_dod_computed']['formatted'] }}</span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    <button type="button" class="btn btn-outline btn-sm"
                            onclick="toggleSection('member-form-{{ $m['model']->id }}')">Edit</button>
                </td>
            </tr>
            {{-- Inline edit form --}}
            <tr id="member-form-{{ $m['model']->id }}" style="display:none">
                <td colspan="7" style="padding:1rem;background:var(--bg-card);border-top:1px solid var(--border-dim)">
                    <form method="POST" action="{{ route('family.member.update', $m['model']->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="grid-2" style="gap:1rem;margin-bottom:1rem">
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" required
                                       value="{{ old('first_name', $m['model']->first_name) }}">
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" required
                                       value="{{ old('last_name', $m['model']->last_name) }}">
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label">Hebrew Name</label>
                                <input type="text" name="hebrew_name" class="form-control"
                                       value="{{ old('hebrew_name', $m['model']->hebrew_name) }}"
                                       style="direction:rtl;font-family:serif">
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-control">
                                    <option value="male" {{ $m['model']->gender === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ $m['model']->gender === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ $m['model']->gender === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-control">
                                    <option value="parent" {{ $m['model']->role === 'parent' ? 'selected' : '' }}>Adult</option>
                                    <option value="child" {{ $m['model']->role === 'child' ? 'selected' : '' }}>Child</option>
                                    <option value="other" {{ $m['model']->role === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control"
                                       value="{{ old('date_of_birth', $m['model']->date_of_birth?->format('Y-m-d')) }}">
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label">Hebrew Birthday Override <span class="text-muted text-sm">(leave blank to auto-calculate)</span></label>
                                <input type="text" name="hebrew_date_of_birth" class="form-control"
                                       value="{{ old('hebrew_date_of_birth', $m['model']->hebrew_date_of_birth) }}"
                                       placeholder="e.g. כ״ה אדר תשפ״ה">
                                <input type="hidden" name="hebrew_dob_override"
                                       value="{{ $m['model']->hebrew_dob_override ? '1' : '0' }}">
                            </div>
                            @if($m['model']->isDeceased())
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label">Date of Death</label>
                                <input type="date" name="date_of_death" class="form-control"
                                       value="{{ old('date_of_death', $m['model']->date_of_death?->format('Y-m-d')) }}">
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label class="form-label">Hebrew Yahrzeit Override</label>
                                <input type="text" name="hebrew_date_of_death" class="form-control"
                                       value="{{ old('hebrew_date_of_death', $m['model']->hebrew_date_of_death) }}"
                                       placeholder="e.g. ד׳ ניסן תשנ״ח">
                                <input type="hidden" name="hebrew_dod_override"
                                       value="{{ $m['model']->hebrew_dod_override ? '1' : '0' }}">
                            </div>
                            @endif
                        </div>
                        <div style="display:flex;gap:0.75rem">
                            <button type="submit" class="btn btn-gold btn-sm">Save</button>
                            <button type="button" class="btn btn-outline btn-sm"
                                    onclick="toggleSection('member-form-{{ $m['model']->id }}')">Cancel</button>
                        </div>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Add Family Member --}}
<div class="card" style="margin-top:1.5rem">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem">
        <div class="card-title" style="margin-bottom:0">Add Family Member</div>
        <button type="button" class="btn btn-outline btn-sm" onclick="toggleSection('add-member-form')">+ Add</button>
    </div>
    <div id="add-member-form" style="display:none">
        <form method="POST" action="{{ route('family.member.add') }}">
            @csrf
            <div class="grid-2" style="gap:1rem;margin-bottom:1rem">
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">First Name <span style="color:var(--gold)">*</span></label>
                    <input type="text" name="first_name" class="form-control" required value="{{ old('first_name') }}">
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Last Name <span style="color:var(--gold)">*</span></label>
                    <input type="text" name="last_name" class="form-control" required value="{{ old('last_name') }}">
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Hebrew Name</label>
                    <input type="text" name="hebrew_name" class="form-control" value="{{ old('hebrew_name') }}"
                           style="direction:rtl;font-family:serif" placeholder="שם עברי">
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Gender <span style="color:var(--gold)">*</span></label>
                    <select name="gender" class="form-control" required>
                        <option value="">Select...</option>
                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Role <span style="color:var(--gold)">*</span></label>
                    <select name="role" class="form-control" required>
                        <option value="">Select...</option>
                        <option value="parent" {{ old('role') === 'parent' ? 'selected' : '' }}>Adult</option>
                        <option value="child" {{ old('role') === 'child' ? 'selected' : '' }}>Child</option>
                        <option value="other" {{ old('role') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                </div>
            </div>
            <div style="display:flex;gap:0.75rem">
                <button type="submit" class="btn btn-gold btn-sm">Add Member</button>
                <button type="button" class="btn btn-outline btn-sm" onclick="toggleSection('add-member-form')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleSection(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = el.style.display === 'none' ? '' : 'none';
}
@if($errors->any() || session('success'))
// Re-open the form if there were validation errors or success on the contact form
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
    // closed on success
    @elseif($errors->any())
    document.getElementById('contact-form')?.style && (document.getElementById('contact-form').style.display = '');
    @endif
});
@endif
</script>

{{-- Hebrew Keyboard Overlay --}}
<style>
.heb-key {
    background: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 5px 0;
    width: 30px;
    text-align: center;
    cursor: pointer;
    font-family: serif;
    font-size: 1.05rem;
    color: #1a2d5a;
    transition: background 0.1s;
    line-height: 1.4;
}
.heb-key:hover  { background: #e8e8e8; }
.heb-key:active { background: #d0d0d0; }
</style>

<div id="heb-kb" role="dialog" aria-label="Hebrew keyboard"
     style="display:none;position:fixed;z-index:9999;background:#fff;border:1px solid #ddd;
            border-radius:12px;padding:0.75rem;box-shadow:0 8px 32px rgba(0,0,0,0.18);
            width:316px;user-select:none;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.6rem">
        <span style="font-size:0.75rem;font-weight:600;color:#888;letter-spacing:0.05em;text-transform:uppercase">Hebrew Keyboard</span>
        <button type="button" id="heb-kb-close"
                style="background:none;border:none;cursor:pointer;font-size:1.1rem;color:#999;line-height:1;padding:2px 4px">✕</button>
    </div>
    <div id="heb-kb-keys" style="display:flex;flex-wrap:wrap;gap:4px;direction:rtl"></div>
    <div style="display:flex;gap:4px;margin-top:6px">
        <button type="button" class="heb-key" data-char=" "
                style="flex:2;width:auto;font-size:0.72rem;color:#666;font-family:inherit">Space</button>
        <button type="button" class="heb-key" data-char="__bs__"
                style="flex:2;width:auto;font-size:0.85rem">⌫</button>
    </div>
</div>

<script>
(function () {
    var LETTERS = ['א','ב','ג','ד','ה','ו','ז','ח','ט','י',
                   'כ','ך','ל','מ','ם','נ','ן','ס','ע','פ',
                   'ף','צ','ץ','ק','ר','ש','ת'];

    var kb         = document.getElementById('heb-kb');
    var keysDiv    = document.getElementById('heb-kb-keys');
    var activeInput = null;

    // Build letter buttons
    LETTERS.forEach(function (ch) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'heb-key';
        btn.dataset.char = ch;
        btn.textContent = ch;
        keysDiv.appendChild(btn);
    });

    // Delegate clicks inside keyboard
    kb.addEventListener('mousedown', function (e) {
        e.preventDefault(); // keep focus on input
        var btn = e.target.closest('.heb-key');
        if (!btn) return;
        var ch = btn.dataset.char;
        if (ch === '__bs__') deleteChar();
        else insertChar(ch);
    });

    document.getElementById('heb-kb-close').addEventListener('click', hideKb);

    // Close on outside click
    document.addEventListener('mousedown', function (e) {
        if (kb.style.display === 'none') return;
        if (!kb.contains(e.target) && !e.target.closest('[data-heb-trigger]')) {
            hideKb();
        }
    });

    function showKb(input) {
        activeInput = input;
        positionNear(input);
        kb.style.display = 'block';
    }
    function hideKb() { kb.style.display = 'none'; }

    function positionNear(input) {
        var rect    = input.getBoundingClientRect();
        var kbW     = 316;
        var left    = Math.max(6, Math.min(rect.left, window.innerWidth - kbW - 10));
        var topBelow = rect.bottom + 6;
        var topAbove = rect.top - 215;
        var top = (topBelow + 215 > window.innerHeight && topAbove > 6) ? topAbove : topBelow;
        kb.style.left = left + 'px';
        kb.style.top  = top  + 'px';
    }

    function insertChar(ch) {
        if (!activeInput) return;
        var s = activeInput.selectionStart, e = activeInput.selectionEnd;
        activeInput.value = activeInput.value.slice(0, s) + ch + activeInput.value.slice(e);
        activeInput.setSelectionRange(s + 1, s + 1);
        activeInput.focus();
    }

    function deleteChar() {
        if (!activeInput) return;
        var s = activeInput.selectionStart, e = activeInput.selectionEnd;
        if (s !== e) {
            activeInput.value = activeInput.value.slice(0, s) + activeInput.value.slice(e);
            activeInput.setSelectionRange(s, s);
        } else if (s > 0) {
            activeInput.value = activeInput.value.slice(0, s - 1) + activeInput.value.slice(s);
            activeInput.setSelectionRange(s - 1, s - 1);
        }
        activeInput.focus();
    }

    // Attach trigger button beside every Hebrew name input
    document.querySelectorAll('input[name="hebrew_name"]').forEach(function (input) {
        var wrap = document.createElement('div');
        wrap.style.cssText = 'display:flex;gap:6px;align-items:center';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        input.style.flex = '1';

        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.setAttribute('data-heb-trigger', '1');
        trigger.title = 'Open Hebrew keyboard';
        trigger.textContent = 'א';
        trigger.style.cssText =
            'padding:0 10px;height:38px;font-family:serif;font-size:1.2rem;' +
            'background:#f5f5f5;border:1px solid #ddd;border-radius:6px;cursor:pointer;' +
            'flex-shrink:0;color:#1a2d5a;transition:background 0.15s';
        trigger.addEventListener('mouseover', function () { this.style.background = '#e8e8e8'; });
        trigger.addEventListener('mouseout',  function () { this.style.background = '#f5f5f5'; });
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            if (kb.style.display === 'none' || activeInput !== input) showKb(input);
            else hideKb();
        });
        wrap.appendChild(trigger);
    });
})();
</script>
@endif
@endsection
