<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect PayPal — QuickShul Setup</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Inter', sans-serif;
            background: #f5f4f0;
            min-height: 100vh;
            display: flex; align-items: flex-start; justify-content: center;
            padding: 40px 16px;
        }
        .container { width: 100%; max-width: 520px; }
        .brand { text-align: center; margin-bottom: 32px; }
        .brand-name { font-size: 28px; font-weight: 700; color: #1a2d5a; letter-spacing: -0.5px; }
        .brand-name span { color: #c9a84c; }
        .progress {
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 28px;
        }
        .step-dot {
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 600; position: relative; z-index: 1;
        }
        .step-dot.done { background: #22c55e; color: #fff; }
        .step-dot.active { background: #1a2d5a; color: #fff; }
        .step-dot.inactive { background: #ddd; color: #999; }
        .step-line { flex: 1; height: 2px; background: #ddd; max-width: 60px; }
        .step-line.done { background: #1a2d5a; }
        .step-label { text-align: center; font-size: 12px; color: #666; margin-top: 8px; }
        .step-label strong { color: #1a2d5a; }
        .card {
            background: #fff; border-radius: 12px; padding: 36px 40px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08); margin-top: 20px;
        }
        .card h1 { font-size: 22px; color: #1a2d5a; font-weight: 700; margin-bottom: 8px; }
        .card .subtitle { font-size: 14px; color: #666; margin-bottom: 24px; line-height: 1.6; }
        .info-box {
            background: #fffbeb; border: 1px solid #fde68a;
            border-radius: 8px; padding: 14px 16px; margin-bottom: 24px;
            font-size: 13px; color: #92400e; line-height: 1.5;
        }
        .info-box strong { display: block; margin-bottom: 4px; color: #78350f; }
        .connected-badge {
            display: flex; align-items: center; gap: 10px;
            background: #f0fdf4; border: 1px solid #bbf7d0;
            border-radius: 8px; padding: 14px 16px; margin-bottom: 20px;
            font-size: 14px; color: #166534;
        }
        .connected-badge .icon { font-size: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #1a2d5a; margin-bottom: 6px; }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 10px 14px; border: 1.5px solid #ddd;
            border-radius: 8px; font-size: 15px; color: #222;
            transition: border-color 0.15s; outline: none;
            font-family: monospace;
        }
        input:focus { border-color: #1a2d5a; }
        .radio-group { display: flex; gap: 12px; margin-top: 4px; }
        .radio-option {
            flex: 1; display: flex; align-items: center; gap: 8px;
            padding: 10px 14px; border: 1.5px solid #ddd; border-radius: 8px;
            cursor: pointer; transition: border-color 0.15s, background 0.15s;
        }
        .radio-option:has(input:checked) {
            border-color: #1a2d5a; background: #eef2ff;
        }
        .radio-option input[type="radio"] { accent-color: #1a2d5a; }
        .radio-option .mode-label { font-size: 14px; font-weight: 600; color: #1a2d5a; }
        .radio-option .mode-desc { font-size: 12px; color: #666; }
        .hint { font-size: 12px; color: #888; margin-top: 5px; }
        .error-field {
            background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px;
            padding: 12px 16px; margin-bottom: 20px; font-size: 13px; color: #dc2626;
        }
        .flash-success {
            background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;
            padding: 12px 16px; margin-bottom: 20px; font-size: 13px; color: #166534;
        }
        .btn-primary {
            width: 100%; padding: 13px; background: #1a2d5a; color: #fff;
            border: none; border-radius: 8px; font-size: 16px; font-weight: 600;
            cursor: pointer; margin-top: 8px; transition: background 0.15s;
        }
        .btn-primary:hover { background: #142248; }
        .skip-link {
            display: block; text-align: center; font-size: 14px;
            color: #888; text-decoration: none; margin-top: 16px;
        }
        .skip-link:hover { color: #1a2d5a; }
        @media (max-width: 560px) { .card { padding: 24px 20px; } }
    </style>
</head>
<body>
<div class="container">
    <div class="brand">
        <div class="brand-name">Quick<span>Shul</span></div>
    </div>

    <div class="progress">
        <div class="step-dot done">✓</div>
        <div class="step-line done"></div>
        <div class="step-dot done">✓</div>
        <div class="step-line done"></div>
        <div class="step-dot active">3</div>
        <div class="step-line"></div>
        <div class="step-dot inactive">4</div>
    </div>
    <div class="step-label"><strong>Step 3 of 4</strong> — Connect PayPal</div>

    <div class="card">
        <h1>Accept online payments</h1>
        <p class="subtitle">
            Connect your PayPal account so members can pay dues and make donations online.
            All payments go <strong>directly to your PayPal account</strong> — QuickShul never
            touches your funds.
        </p>

        @if(session('success'))
            <div class="flash-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="error-field">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if($tenant->isPayPalConnected())
            <div class="connected-badge">
                <span class="icon">✅</span>
                <div>
                    <strong>PayPal connected</strong>
                    Mode: {{ strtoupper($tenant->paypal_mode ?? 'live') }}
                </div>
            </div>
            <a href="{{ route('register.step4') }}" class="btn-primary" style="display:block;text-align:center;text-decoration:none;">Continue to Step 4 &rarr;</a>
        @else
            <div class="info-box">
                <strong>Where do I find these credentials?</strong>
                Log in to <a href="https://developer.paypal.com/dashboard/" target="_blank" style="color:#92400e;">developer.paypal.com</a>,
                go to Apps &amp; Credentials, and create or select an app to get your Client ID and Secret.
                Use "Live" credentials for real payments.
            </div>

            <form method="POST" action="{{ route('register.paypal.connect') }}">
                @csrf

                <div class="form-group">
                    <label for="paypal_client_id">PayPal Client ID</label>
                    <input type="text" id="paypal_client_id" name="paypal_client_id"
                           value="{{ old('paypal_client_id') }}"
                           placeholder="AYour-PayPal-Client-ID-Here" required>
                </div>

                <div class="form-group">
                    <label for="paypal_secret">PayPal Secret</label>
                    <input type="password" id="paypal_secret" name="paypal_secret"
                           placeholder="Your PayPal Secret" required>
                    <div class="hint">Your secret is stored encrypted and never displayed again.</div>
                </div>

                <div class="form-group">
                    <label>Mode</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="paypal_mode" value="live"
                                   {{ old('paypal_mode', 'live') === 'live' ? 'checked' : '' }}>
                            <div>
                                <div class="mode-label">Live</div>
                                <div class="mode-desc">Real payments</div>
                            </div>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="paypal_mode" value="sandbox"
                                   {{ old('paypal_mode') === 'sandbox' ? 'checked' : '' }}>
                            <div>
                                <div class="mode-label">Sandbox</div>
                                <div class="mode-desc">Testing only</div>
                            </div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Connect PayPal &rarr;</button>
            </form>
        @endif

        <form method="POST" action="{{ route('register.paypal.skip') }}" style="margin-top:8px;">
            @csrf
            <button type="submit" style="background:none;border:none;width:100%;cursor:pointer;">
                <span class="skip-link">Skip for now — I'll connect PayPal later</span>
            </button>
        </form>
    </div>
</div>
</body>
</html>
