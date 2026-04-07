<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your QuickShul Portal</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Inter', sans-serif;
            background: #f5f4f0;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 40px 16px;
        }
        .container {
            width: 100%;
            max-width: 520px;
        }
        .brand {
            text-align: center;
            margin-bottom: 32px;
        }
        .brand-name {
            font-size: 28px;
            font-weight: 700;
            color: #1a2d5a;
            letter-spacing: -0.5px;
        }
        .brand-name span { color: #c9a84c; }
        .brand-tagline {
            font-size: 14px;
            color: #666;
            margin-top: 6px;
        }
        /* Progress */
        .progress {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 28px;
            gap: 0;
        }
        .step-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
            background: #1a2d5a;
            color: #fff;
            position: relative;
            z-index: 1;
        }
        .step-dot.inactive {
            background: #ddd;
            color: #999;
        }
        .step-line {
            flex: 1;
            height: 2px;
            background: #ddd;
            max-width: 60px;
        }
        .step-line.done { background: #1a2d5a; }
        .step-label {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 8px;
        }
        .step-label strong { color: #1a2d5a; }
        /* Card */
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 36px 40px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08);
        }
        .card h1 {
            font-size: 22px;
            color: #1a2d5a;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .card .subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 28px;
        }
        /* Form */
        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #1a2d5a;
            margin-bottom: 6px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            color: #222;
            transition: border-color 0.15s;
            outline: none;
        }
        input:focus { border-color: #1a2d5a; }
        input.is-valid { border-color: #22c55e; }
        input.is-invalid { border-color: #ef4444; }
        .slug-row {
            display: flex;
            align-items: center;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: border-color 0.15s;
        }
        .slug-row:focus-within { border-color: #1a2d5a; }
        .slug-row.is-valid { border-color: #22c55e; }
        .slug-row.is-invalid { border-color: #ef4444; }
        .slug-prefix {
            padding: 10px 12px;
            background: #f5f4f0;
            font-size: 13px;
            color: #888;
            white-space: nowrap;
            border-right: 1.5px solid #ddd;
        }
        .slug-suffix {
            padding: 10px 12px;
            background: #f5f4f0;
            font-size: 13px;
            color: #888;
            white-space: nowrap;
            border-left: 1.5px solid #ddd;
        }
        .slug-input {
            flex: 1;
            border: none !important;
            padding: 10px 12px;
            font-size: 15px;
            outline: none;
            min-width: 0;
        }
        .slug-status {
            font-size: 12px;
            margin-top: 5px;
            min-height: 18px;
        }
        .slug-status.available { color: #22c55e; }
        .slug-status.taken { color: #ef4444; }
        .slug-status.checking { color: #888; }
        .portal-preview {
            font-size: 13px;
            color: #1a2d5a;
            background: #eef2ff;
            border-radius: 6px;
            padding: 8px 12px;
            margin-top: 8px;
            display: none;
        }
        .portal-preview strong { color: #c9a84c; }
        .hint { font-size: 12px; color: #888; margin-top: 5px; }
        .error-msg { font-size: 12px; color: #ef4444; margin-top: 5px; }
        .btn-primary {
            width: 100%;
            padding: 13px;
            background: #1a2d5a;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.15s;
        }
        .btn-primary:hover { background: #142248; }
        .btn-google {
            width: 100%;
            padding: 12px;
            background: #fff;
            color: #333;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            margin-bottom: 4px;
        }
        .btn-google:hover { border-color: #aaa; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
            color: #bbb;
            font-size: 13px;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #eee;
        }
        .google-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f0f7ff;
            border: 1.5px solid #b8d4f0;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #1a2d5a;
        }
        .google-badge .change-link {
            margin-left: auto;
            font-size: 12px;
            color: #888;
            text-decoration: none;
            white-space: nowrap;
        }
        .google-badge .change-link:hover { color: #e74c3c; }
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .login-link a { color: #1a2d5a; font-weight: 600; text-decoration: none; }
        @media (max-width: 560px) {
            .card { padding: 24px 20px; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="brand">
        <div class="brand-name">Quick<span>Shul</span></div>
        <div class="brand-tagline">Member management for modern synagogues</div>
    </div>

    <!-- Progress -->
    <div class="progress">
        <div class="step-dot">1</div>
        <div class="step-line"></div>
        <div class="step-dot inactive">2</div>
        <div class="step-line"></div>
        <div class="step-dot inactive">3</div>
        <div class="step-line"></div>
        <div class="step-dot inactive">4</div>
    </div>
    <div class="step-label"><strong>Step 1 of 4</strong> — Create Your Account</div>

    <div class="card" style="margin-top:20px">
        <h1>Set up your shul portal</h1>
        <p class="subtitle">Takes about 2 minutes. No credit card required.</p>

        @if(session('error'))
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#dc2626;">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#dc2626;">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @php $google = session('register_google'); @endphp

        @if(!$google && config('services.google.client_id'))
            {{-- Google sign-up button (only when no Google data yet) --}}
            <a href="{{ route('register.google') }}" class="btn-google">
                <svg width="18" height="18" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z"/><path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.964 10.707A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.707V4.961H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.039l3.007-2.332z"/><path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.961L3.964 6.293C4.672 4.166 6.656 3.58 9 3.58z"/></svg>
                Continue with Google
            </a>
            <div class="divider">or sign up with email</div>
        @endif

        @if($google)
            {{-- Show the connected Google account --}}
            <div class="google-badge">
                <svg width="16" height="16" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z"/><path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.964 10.707A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.707V4.961H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.039l3.007-2.332z"/><path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.961L3.964 6.293C4.672 4.166 6.656 3.58 9 3.58z"/></svg>
                <span>Signing up as <strong>{{ $google['email'] }}</strong></span>
                <a href="{{ route('register') }}?forget_google=1" class="change-link">Use different account</a>
            </div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}" id="regForm">
            @csrf

            <div class="form-group">
                <label for="org_name">Synagogue / Organization Name</label>
                <input type="text" id="org_name" name="org_name" value="{{ old('org_name') }}"
                       placeholder="Beth Israel Congregation" required>
            </div>

            <div class="form-group">
                <label for="slug">Your Portal Subdomain</label>
                <div class="slug-row" id="slugRow">
                    <span class="slug-prefix">https://</span>
                    <input type="text" id="slug" name="slug" class="slug-input"
                           value="{{ old('slug') }}" placeholder="beth-israel"
                           autocomplete="off" spellcheck="false"
                           minlength="3" maxlength="30" required>
                    <span class="slug-suffix">.quickshul.com</span>
                </div>
                <div class="slug-status" id="slugStatus"></div>
                <div class="portal-preview" id="portalPreview">
                    Your portal will be at: <strong id="previewUrl"></strong>
                </div>
                <div class="hint">3–30 characters: lowercase letters, numbers, and hyphens only.</div>
                @error('slug')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="admin_name">Your Name</label>
                <input type="text" id="admin_name" name="admin_name"
                       value="{{ old('admin_name', $google['name'] ?? '') }}"
                       placeholder="Rabbi David Cohen" required>
            </div>

            @if(!$google)
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       placeholder="rabbi@bethisrael.org" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="At least 8 characters" minlength="8" required>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       placeholder="Repeat your password" required>
            </div>
            @else
            {{-- Hidden fields when signing up via Google --}}
            <input type="hidden" name="email" value="{{ $google['email'] }}">
            @endif

            <button type="submit" class="btn-primary" id="submitBtn">
                Create My Portal &rarr;
            </button>
        </form>
    </div>

    <div class="login-link">
        Already have a portal? <a href="/login">Sign in</a>
    </div>
</div>

<script>
    const slugInput   = document.getElementById('slug');
    const slugRow     = document.getElementById('slugRow');
    const slugStatus  = document.getElementById('slugStatus');
    const previewEl   = document.getElementById('portalPreview');
    const previewUrl  = document.getElementById('previewUrl');
    let debounceTimer = null;
    let lastChecked   = '';

    function updatePreview(val) {
        if (val.length >= 3) {
            previewUrl.textContent = val + '.quickshul.com';
            previewEl.style.display = 'block';
        } else {
            previewEl.style.display = 'none';
        }
    }

    function checkSlug(slug) {
        if (slug === lastChecked) return;
        lastChecked = slug;

        if (slug.length < 3) {
            slugStatus.textContent = '';
            slugRow.classList.remove('is-valid', 'is-invalid');
            return;
        }

        slugStatus.className = 'slug-status checking';
        slugStatus.textContent = 'Checking availability…';

        fetch('/register/check-slug?slug=' + encodeURIComponent(slug))
            .then(r => r.json())
            .then(data => {
                if (data.available) {
                    slugStatus.className = 'slug-status available';
                    slugStatus.textContent = '✓ Available!';
                    slugRow.classList.add('is-valid');
                    slugRow.classList.remove('is-invalid');
                } else {
                    slugStatus.className = 'slug-status taken';
                    slugStatus.textContent = data.reason === 'reserved'
                        ? '✗ That subdomain is reserved.'
                        : '✗ Already taken. Please choose another.';
                    slugRow.classList.add('is-invalid');
                    slugRow.classList.remove('is-valid');
                }
            })
            .catch(() => {
                slugStatus.textContent = '';
            });
    }

    slugInput.addEventListener('input', function () {
        // Auto-format: lowercase, replace spaces with hyphens, strip invalid chars
        const raw = this.value.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
        if (raw !== this.value) this.value = raw;

        updatePreview(raw);
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => checkSlug(raw), 500);
    });

    // Check on load if there's a pre-filled value (e.g. validation error)
    if (slugInput.value) {
        updatePreview(slugInput.value);
        checkSlug(slugInput.value);
    }
</script>
</body>
</html>
