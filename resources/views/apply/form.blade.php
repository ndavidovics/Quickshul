<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Application &mdash; {{ $tenant->name ?? config('app.name') }}</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:      #1a2d5a;
            --navy-dark: #111d3c;
            --gold:      #c9a84c;
            --cream:     #faf8f4;
            --text:      #2c2c2c;
            --text-light:#6b6b6b;
            --border:    #ddd6c8;
            --error:     #c0392b;
            --white:     #ffffff;
            --success:   #27ae60;
        }

        body {
            min-height: 100vh;
            background-color: var(--cream);
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(26,45,90,0.06) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(201,168,76,0.08) 0%, transparent 50%);
            font-family: 'Inter', sans-serif;
            color: var(--text);
            padding: 2rem 1rem 4rem;
        }

        .page-wrapper { width: 100%; max-width: 700px; margin: 0 auto; }

        .brand-header { text-align: center; margin-bottom: 2rem; }
        .logo-container { display: inline-block; background: var(--white); border-radius: 50%; padding: 10px; box-shadow: 0 4px 20px rgba(26,45,90,0.12); margin-bottom: 1rem; border: 3px solid var(--gold); }
        .logo-container img { width: 72px; height: 72px; object-fit: contain; display: block; border-radius: 50%; }
        .brand-name { font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 700; color: var(--navy); }
        .brand-subtitle { font-size: 0.78rem; font-weight: 500; letter-spacing: 0.12em; text-transform: uppercase; color: var(--gold); margin-top: 0.2rem; }
        .brand-tagline { font-size: 0.85rem; color: var(--text-light); margin-top: 0.6rem; }

        .card { background: var(--white); border-radius: 16px; padding: 2rem 1.75rem; box-shadow: 0 4px 6px rgba(26,45,90,0.04), 0 10px 40px rgba(26,45,90,0.08); border: 1px solid rgba(201,168,76,0.15); margin-bottom: 1.25rem; }
        .card-title { font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 600; color: var(--navy); margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border); }

        .section-num { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; background: var(--navy); color: white; border-radius: 50%; font-size: 0.72rem; font-weight: 700; margin-right: 0.5rem; flex-shrink: 0; }

        /* Membership type cards */
        .membership-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
        @media (max-width: 500px) { .membership-grid { grid-template-columns: 1fr; } }

        .membership-card { position: relative; cursor: pointer; }
        .membership-card input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
        .membership-card label {
            display: block; padding: 1rem 1.1rem; border: 2px solid var(--border); border-radius: 12px;
            cursor: pointer; transition: all 0.2s; background: var(--cream);
        }
        .membership-card input[type="radio"]:checked + label {
            border-color: var(--navy); background: rgba(26,45,90,0.04);
            box-shadow: 0 0 0 3px rgba(26,45,90,0.1);
        }
        .membership-card label:hover { border-color: var(--gold); }
        .membership-type-name { font-weight: 700; color: var(--navy); font-size: 0.95rem; }
        .membership-type-price { font-family: 'Playfair Display', serif; font-size: 1.3rem; color: var(--gold); font-weight: 700; margin: 0.25rem 0; }
        .membership-type-desc { font-size: 0.75rem; color: var(--text-light); }

        /* Form fields */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }
        .form-grid .full { grid-column: 1 / -1; }

        .form-group { margin-bottom: 0; }
        label { display: block; font-size: 0.78rem; font-weight: 600; color: var(--navy); margin-bottom: 0.35rem; letter-spacing: 0.02em; }
        label .opt { color: var(--text-light); font-weight: 400; }

        input[type="text"], input[type="email"], input[type="tel"], input[type="date"], select, textarea {
            width: 100%; padding: 0.65rem 0.875rem; border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 0.9rem; font-family: 'Inter', sans-serif; color: var(--text); background: var(--cream);
            transition: border-color 0.2s, box-shadow 0.2s; outline: none;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--navy); background: var(--white); box-shadow: 0 0 0 3px rgba(26,45,90,0.08);
        }
        textarea { resize: vertical; min-height: 80px; }
        .field-error { font-size: 0.75rem; color: var(--error); margin-top: 0.3rem; }

        /* Alerts */
        .alert-error { background: #fdf3f2; border: 1px solid #f5c6c2; border-left: 3px solid var(--error); border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.85rem; color: var(--error); margin-bottom: 1.25rem; }

        /* Member rows */
        .member-row { background: var(--cream); border: 1px solid var(--border); border-radius: 10px; padding: 1rem; margin-bottom: 0.75rem; position: relative; }
        .member-row-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
        .member-row-title { font-weight: 600; color: var(--navy); font-size: 0.85rem; }
        .btn-remove { background: none; border: none; color: var(--error); font-size: 0.8rem; cursor: pointer; padding: 0.2rem 0.5rem; border-radius: 6px; }
        .btn-remove:hover { background: rgba(192,57,43,0.08); }

        /* Email rows */
        .email-row { display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center; }
        .email-row input { flex: 1; }

        /* Buttons */
        .btn-add { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.9rem; background: transparent; border: 1.5px dashed var(--navy); border-radius: 8px; color: var(--navy); font-size: 0.82rem; font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif; transition: all 0.15s; margin-top: 0.25rem; }
        .btn-add:hover { background: rgba(26,45,90,0.05); }

        .btn-submit { width: 100%; padding: 0.9rem 1rem; background: linear-gradient(135deg, var(--navy) 0%, var(--navy-dark) 100%); border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; color: white; font-family: 'Inter', sans-serif; cursor: pointer; transition: all 0.2s; letter-spacing: 0.02em; margin-top: 0.5rem; }
        .btn-submit:hover { background: linear-gradient(135deg, #22397a 0%, #1a2d5a 100%); box-shadow: 0 4px 15px rgba(26,45,90,0.35); transform: translateY(-1px); }
        .btn-submit:active { transform: translateY(0); }

        .back-link { display: block; text-align: center; margin-bottom: 1.25rem; font-size: 0.82rem; color: var(--text-light); text-decoration: none; }
        .back-link:hover { color: var(--navy); }

        .divider-label { text-align: center; font-size: 0.72rem; color: var(--text-light); letter-spacing: 0.08em; text-transform: uppercase; margin: 1.5rem 0 1rem; }
    </style>
</head>
<body>
<div class="page-wrapper">

    <div class="brand-header">
        <div class="logo-container">
            <img src="{{ $tenant->logo_url ?? asset('img/quickshul-logo.svg') }}" alt="{{ $tenant->name ?? config('app.name') }}">
        </div>
        <div class="brand-name">{{ $tenant->name ?? config('app.name') }}</div>
        <div class="brand-subtitle">Membership Application</div>
        <div class="brand-tagline">We're glad you're joining our community. Please fill out the form below.</div>
    </div>

    <a href="{{ route('login') }}" class="back-link">← Already a member? Sign in</a>

    @if($errors->any())
    <div class="alert-error">
        <strong>Please correct the following:</strong>
        <ul style="margin-top:0.4rem;padding-left:1.25rem">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('apply.submit') }}" id="apply-form">
        @csrf

        {{-- Section 1: Membership Type --}}
        <div class="card">
            <div class="card-title"><span class="section-num">1</span> Choose Membership Level</div>
            <div class="membership-grid">
                @php
                $types = [
                    'full_family'     => ['name'=>'Full Family',    'price'=>'$1,800/yr', 'desc'=>'For families with children'],
                    'associate'       => ['name'=>'Associate',      'price'=>'$750/yr',   'desc'=>'For couples without children at home'],
                    'single'          => ['name'=>'Single',         'price'=>'$900/yr',   'desc'=>'Individual membership'],
                    'first_year_free' => ['name'=>'Complimentary',  'price'=>'$0',        'desc'=>'First Year Free / Special Circumstances'],
                ];
                @endphp
                @foreach($types as $value => $type)
                <div class="membership-card">
                    <input type="radio" name="membership_type" id="mt_{{ $value }}" value="{{ $value }}"
                           {{ old('membership_type', 'full_family') === $value ? 'checked' : '' }}>
                    <label for="mt_{{ $value }}">
                        <div class="membership-type-name">{{ $type['name'] }}</div>
                        <div class="membership-type-price">{{ $type['price'] }}</div>
                        <div class="membership-type-desc">{{ $type['desc'] }}</div>
                    </label>
                </div>
                @endforeach
            </div>
            @error('membership_type')<div class="field-error" style="margin-top:0.5rem">{{ $message }}</div>@enderror
        </div>

        {{-- Section 2: Family/Contact Info --}}
        <div class="card">
            <div class="card-title"><span class="section-num">2</span> Family & Contact Information</div>
            <div class="form-grid">
                <div class="form-group full">
                    <label for="family_name">Family Name (as it should appear in our records) *</label>
                    <input type="text" id="family_name" name="family_name" required value="{{ old('family_name') }}" placeholder="e.g. The Smith Family">
                    @error('family_name')<div class="field-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group full">
                    <label for="phone">Phone Number <span class="opt">(optional)</span></label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="901-555-1234">
                    @error('phone')<div class="field-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group full">
                    <label for="address">Street Address <span class="opt">(optional)</span></label>
                    <input type="text" id="address" name="address" value="{{ old('address') }}" placeholder="123 Main St">
                    @error('address')<div class="field-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="city">City <span class="opt">(optional)</span></label>
                    <input type="text" id="city" name="city" value="{{ old('city') }}" placeholder="{{ $tenant->city ?? 'City' }}">
                    @error('city')<div class="field-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="state">State <span class="opt">(optional)</span></label>
                    <input type="text" id="state" name="state" value="{{ old('state') }}" placeholder="TN" maxlength="2" style="text-transform:uppercase">
                    @error('state')<div class="field-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="zip">ZIP Code <span class="opt">(optional)</span></label>
                    <input type="text" id="zip" name="zip" value="{{ old('zip') }}" placeholder="38120">
                    @error('zip')<div class="field-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Section 3: Email Addresses --}}
        <div class="card">
            <div class="card-title"><span class="section-num">3</span> Email Address(es)</div>
            <p style="font-size:0.82rem;color:var(--text-light);margin-bottom:1rem">These will be used to log in to the member portal. At least one is required.</p>
            <div id="emails-container">
                @php $oldEmails = old('emails', ['']); @endphp
                @foreach($oldEmails as $i => $em)
                <div class="email-row" id="email-row-{{ $i }}">
                    <input type="email" name="emails[]" value="{{ $em }}" placeholder="you@example.com"
                           {{ $i === 0 ? 'required' : '' }}>
                    @if($i > 0)
                    <button type="button" class="btn-remove" onclick="removeEmail({{ $i }})">✕</button>
                    @endif
                </div>
                @endforeach
            </div>
            <button type="button" class="btn-add" onclick="addEmail()">+ Add another email</button>
            @error('emails')<div class="field-error" style="margin-top:0.5rem">{{ $message }}</div>@enderror
            @error('emails.*')<div class="field-error" style="margin-top:0.5rem">{{ $message }}</div>@enderror
        </div>

        {{-- Section 4: Family Members --}}
        <div class="card">
            <div class="card-title"><span class="section-num">4</span> Family Members</div>
            <p style="font-size:0.82rem;color:var(--text-light);margin-bottom:1rem">Please add all members of the household. At least one is required.</p>
            <div id="members-container">
                @php $oldMembers = old('members', [[]]); @endphp
                @foreach($oldMembers as $i => $m)
                <div class="member-row" id="member-row-{{ $i }}">
                    <div class="member-row-header">
                        <span class="member-row-title">Member {{ $i + 1 }}</span>
                        @if($i > 0)
                        <button type="button" class="btn-remove" onclick="removeMember({{ $i }})">Remove</button>
                        @endif
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" name="members[{{ $i }}][first_name]" required value="{{ $m['first_name'] ?? '' }}">
                            @error("members.{$i}.first_name")<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Last Name *</label>
                            <input type="text" name="members[{{ $i }}][last_name]" required value="{{ $m['last_name'] ?? '' }}">
                            @error("members.{$i}.last_name")<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Gender *</label>
                            <select name="members[{{ $i }}][gender]" required>
                                <option value="">— Select —</option>
                                <option value="male"   {{ ($m['gender'] ?? '') === 'male'   ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ ($m['gender'] ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other"  {{ ($m['gender'] ?? '') === 'other'  ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Role *</label>
                            <select name="members[{{ $i }}][role]" required>
                                <option value="">— Select —</option>
                                <option value="parent" {{ ($m['role'] ?? '') === 'parent' ? 'selected' : '' }}>Parent / Adult</option>
                                <option value="child"  {{ ($m['role'] ?? '') === 'child'  ? 'selected' : '' }}>Child</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date of Birth <span class="opt">(optional)</span></label>
                            <input type="date" name="members[{{ $i }}][date_of_birth]" value="{{ $m['date_of_birth'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Hebrew Name <span class="opt">(optional)</span></label>
                            <input type="text" name="members[{{ $i }}][hebrew_name]" class="heb-input" value="{{ $m['hebrew_name'] ?? '' }}" placeholder="שם עברי" style="direction:rtl;font-family:serif">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <button type="button" class="btn-add" onclick="addMember()">+ Add another family member</button>
        </div>

        {{-- Section 5: Notes --}}
        <div class="card">
            <div class="card-title"><span class="section-num">5</span> Anything Else? <span style="font-size:0.85rem;font-weight:400;font-family:'Inter',sans-serif;color:var(--text-light)">Optional</span></div>
            <div class="form-group">
                <label for="notes">Additional notes or questions for the office</label>
                <textarea id="notes" name="notes" placeholder="Any special circumstances, questions, or information you'd like us to know...">{{ old('notes') }}</textarea>
                @error('notes')<div class="field-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <button type="submit" class="btn-submit">Submit Application</button>
        <p style="text-align:center;font-size:0.78rem;color:var(--text-light);margin-top:1rem">
            Your application will be reviewed by our office. We'll be in touch soon.
        </p>
    </form>

</div>

{{-- Hebrew Keyboard --}}
<style>
.heb-key { background:#f0ede8;border:1px solid #ccc;border-radius:6px;padding:5px 0;width:30px;text-align:center;cursor:pointer;font-family:serif;font-size:1.05rem;color:var(--navy);transition:background 0.1s;line-height:1.4; }
.heb-key:hover  { background:#e0dbd3; }
.heb-key:active { background:#d0cbc1; }
</style>
<div id="heb-kb" style="display:none;position:fixed;z-index:9999;background:#fff;border:1px solid #ccc;border-radius:12px;padding:0.75rem;box-shadow:0 8px 32px rgba(0,0,0,0.18);width:316px;user-select:none;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.6rem">
        <span style="font-size:0.75rem;font-weight:600;color:#888;letter-spacing:0.05em;text-transform:uppercase">Hebrew Keyboard</span>
        <button type="button" id="heb-kb-close" style="background:none;border:none;cursor:pointer;font-size:1.1rem;color:#999;line-height:1;padding:2px 4px">✕</button>
    </div>
    <div id="heb-kb-keys" style="display:flex;flex-wrap:wrap;gap:4px;direction:rtl"></div>
    <div style="display:flex;gap:4px;margin-top:6px">
        <button type="button" class="heb-key" data-char=" " style="flex:2;width:auto;font-size:0.72rem;color:#666;font-family:inherit">Space</button>
        <button type="button" class="heb-key" data-char="__bs__" style="flex:2;width:auto;font-size:0.85rem">⌫</button>
    </div>
</div>

<script>
(function () {
    var LETTERS = ['א','ב','ג','ד','ה','ו','ז','ח','ט','י','כ','ך','ל','מ','ם','נ','ן','ס','ע','פ','ף','צ','ץ','ק','ר','ש','ת'];
    var kb = document.getElementById('heb-kb');
    var keysDiv = document.getElementById('heb-kb-keys');
    var activeInput = null;

    LETTERS.forEach(function (ch) {
        var btn = document.createElement('button');
        btn.type = 'button'; btn.className = 'heb-key'; btn.dataset.char = ch; btn.textContent = ch;
        keysDiv.appendChild(btn);
    });

    kb.addEventListener('mousedown', function (e) {
        e.preventDefault();
        var btn = e.target.closest('.heb-key');
        if (!btn) return;
        var ch = btn.dataset.char;
        if (ch === '__bs__') deleteChar(); else insertChar(ch);
    });

    document.getElementById('heb-kb-close').addEventListener('click', hideKb);

    document.addEventListener('mousedown', function (e) {
        if (kb.style.display === 'none') return;
        if (!kb.contains(e.target) && !e.target.closest('[data-heb-trigger]')) hideKb();
    });

    function showKb(input) { activeInput = input; positionNear(input); kb.style.display = 'block'; }
    function hideKb()      { kb.style.display = 'none'; }

    function positionNear(input) {
        var rect = input.getBoundingClientRect();
        var kbW = 316;
        var left = Math.max(6, Math.min(rect.left, window.innerWidth - kbW - 10));
        var topBelow = rect.bottom + 6;
        var topAbove = rect.top - 215;
        var top = (topBelow + 215 > window.innerHeight && topAbove > 6) ? topAbove : topBelow;
        kb.style.left = left + 'px'; kb.style.top = top + 'px';
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
        if (s !== e) { activeInput.value = activeInput.value.slice(0, s) + activeInput.value.slice(e); activeInput.setSelectionRange(s, s); }
        else if (s > 0) { activeInput.value = activeInput.value.slice(0, s - 1) + activeInput.value.slice(s); activeInput.setSelectionRange(s - 1, s - 1); }
        activeInput.focus();
    }

    window.initHebInput = function (input) {
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
        trigger.style.cssText = 'padding:0 10px;height:38px;font-family:serif;font-size:1.2rem;background:#f0ede8;border:1px solid #ccc;border-radius:6px;cursor:pointer;flex-shrink:0;color:var(--navy);transition:background 0.15s';
        trigger.addEventListener('mouseover', function () { this.style.background = '#e0dbd3'; });
        trigger.addEventListener('mouseout',  function () { this.style.background = '#f0ede8'; });
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            if (kb.style.display === 'none' || activeInput !== input) showKb(input); else hideKb();
        });
        wrap.appendChild(trigger);
    };

    document.querySelectorAll('.heb-input').forEach(window.initHebInput);
})();

let emailCount = {{ count(old('emails', [''])) }};
let memberCount = {{ count(old('members', [[]])) }};

function addEmail() {
    const container = document.getElementById('emails-container');
    const row = document.createElement('div');
    row.className = 'email-row';
    row.id = 'email-row-' + emailCount;
    row.innerHTML = `<input type="email" name="emails[]" placeholder="another@example.com">
                     <button type="button" class="btn-remove" onclick="removeEmail(${emailCount})">✕</button>`;
    container.appendChild(row);
    emailCount++;
}

function removeEmail(i) {
    const row = document.getElementById('email-row-' + i);
    if (row) row.remove();
}

function addMember() {
    const container = document.getElementById('members-container');
    const i = memberCount;
    const row = document.createElement('div');
    row.className = 'member-row';
    row.id = 'member-row-' + i;
    row.innerHTML = `
        <div class="member-row-header">
            <span class="member-row-title">Member ${i + 1}</span>
            <button type="button" class="btn-remove" onclick="removeMember(${i})">Remove</button>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="members[${i}][first_name]" required>
            </div>
            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="members[${i}][last_name]" required>
            </div>
            <div class="form-group">
                <label>Gender *</label>
                <select name="members[${i}][gender]" required>
                    <option value="">— Select —</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Role *</label>
                <select name="members[${i}][role]" required>
                    <option value="">— Select —</option>
                    <option value="parent">Parent / Adult</option>
                    <option value="child">Child</option>
                </select>
            </div>
            <div class="form-group">
                <label>Date of Birth <span class="opt">(optional)</span></label>
                <input type="date" name="members[${i}][date_of_birth]">
            </div>
            <div class="form-group">
                <label>Hebrew Name <span class="opt">(optional)</span></label>
                <input type="text" name="members[${i}][hebrew_name]" class="heb-input" placeholder="שם עברי" style="direction:rtl;font-family:serif">
            </div>
        </div>`;
    container.appendChild(row);
    // Wire up Hebrew keyboard on the newly added input
    var hebInput = row.querySelector('.heb-input');
    if (hebInput && window.initHebInput) window.initHebInput(hebInput);
    memberCount++;
}

function removeMember(i) {
    const row = document.getElementById('member-row-' + i);
    if (row) row.remove();
}
</script>
</body>
</html>
