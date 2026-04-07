<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect Gmail — QuickShul Setup</title>
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
            background: #eff6ff; border: 1px solid #bfdbfe;
            border-radius: 8px; padding: 14px 16px; margin-bottom: 24px;
            font-size: 13px; color: #1e40af; line-height: 1.5;
        }
        .info-box strong { display: block; margin-bottom: 4px; color: #1a2d5a; }
        .connected-badge {
            display: flex; align-items: center; gap: 10px;
            background: #f0fdf4; border: 1px solid #bbf7d0;
            border-radius: 8px; padding: 14px 16px; margin-bottom: 20px;
            font-size: 14px; color: #166534;
        }
        .connected-badge .icon { font-size: 20px; }
        .btn-google {
            width: 100%; padding: 13px 20px;
            background: #fff; color: #1a2d5a;
            border: 2px solid #1a2d5a; border-radius: 8px;
            font-size: 15px; font-weight: 600; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            text-decoration: none; transition: background 0.15s;
            margin-bottom: 12px;
        }
        .btn-google:hover { background: #eef2ff; }
        .btn-google svg { width: 20px; height: 20px; flex-shrink: 0; }
        .btn-primary {
            width: 100%; padding: 13px; background: #1a2d5a; color: #fff;
            border: none; border-radius: 8px; font-size: 15px; font-weight: 600;
            cursor: pointer; text-decoration: none;
            display: block; text-align: center; margin-bottom: 12px;
            transition: background 0.15s;
        }
        .btn-primary:hover { background: #142248; }
        .skip-link {
            display: block; text-align: center; font-size: 14px;
            color: #888; text-decoration: none; margin-top: 4px;
        }
        .skip-link:hover { color: #1a2d5a; }
        .flash-success {
            background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;
            padding: 12px 16px; margin-bottom: 20px; font-size: 13px; color: #166534;
        }
        .flash-error {
            background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px;
            padding: 12px 16px; margin-bottom: 20px; font-size: 13px; color: #dc2626;
        }
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
        <div class="step-dot active">2</div>
        <div class="step-line"></div>
        <div class="step-dot inactive">3</div>
        <div class="step-line"></div>
        <div class="step-dot inactive">4</div>
    </div>
    <div class="step-label"><strong>Step 2 of 4</strong> — Connect Gmail</div>

    <div class="card">
        <h1>Connect your Gmail account</h1>
        <p class="subtitle">
            QuickShul sends all emails — balance reminders, giving statements, announcements —
            directly through your own Gmail account. Members will see emails coming from
            <strong>your address</strong>, not a generic no-reply.
        </p>

        @if(session('success'))
            <div class="flash-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash-error">{{ session('error') }}</div>
        @endif

        @if($tenant->isGmailConnected())
            <div class="connected-badge">
                <span class="icon">✅</span>
                <div>
                    <strong>Gmail connected</strong>
                    Sending from: {{ $tenant->gmail_email }}
                </div>
            </div>
            <a href="{{ route('register.step3') }}" class="btn-primary">Continue to Step 3 &rarr;</a>
        @else
            <div class="info-box">
                <strong>Why connect Gmail?</strong>
                When you connect your Gmail account, QuickShul uses it to send emails on your behalf.
                Members will receive emails from your address and can reply directly to you.
                You can revoke access at any time from your Google account settings.
            </div>

            <a href="{{ route('register.gmail.connect') }}" class="btn-google">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Connect Gmail Account
            </a>
        @endif

        <form method="POST" action="{{ route('register.gmail.skip') }}">
            @csrf
            <button type="submit" style="background:none;border:none;width:100%;cursor:pointer;">
                <span class="skip-link">Skip for now — I'll set this up later</span>
            </button>
        </form>
    </div>
</div>
</body>
</html>
