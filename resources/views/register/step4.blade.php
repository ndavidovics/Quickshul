<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're All Set — QuickShul</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Inter', sans-serif;
            background: #f5f4f0;
            min-height: 100vh;
            display: flex; align-items: flex-start; justify-content: center;
            padding: 40px 16px;
        }
        .container { width: 100%; max-width: 560px; }
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
            font-size: 13px; font-weight: 600;
        }
        .step-dot.done { background: #22c55e; color: #fff; }
        .step-line { flex: 1; height: 2px; background: #22c55e; max-width: 60px; }
        .step-label { text-align: center; font-size: 12px; color: #666; margin-top: 8px; }
        .step-label strong { color: #166534; }
        .card {
            background: #fff; border-radius: 12px; padding: 40px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08); margin-top: 20px;
            text-align: center;
        }
        .celebration { font-size: 48px; margin-bottom: 16px; }
        .card h1 { font-size: 26px; color: #1a2d5a; font-weight: 700; margin-bottom: 8px; }
        .card .subtitle { font-size: 15px; color: #555; margin-bottom: 32px; line-height: 1.6; }
        /* Portal URL */
        .portal-url-box {
            background: #eef2ff; border: 2px solid #c7d2fe;
            border-radius: 10px; padding: 20px; margin-bottom: 28px;
        }
        .portal-url-label { font-size: 12px; font-weight: 600; color: #6366f1; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .portal-url-link {
            font-size: 20px; font-weight: 700; color: #1a2d5a;
            text-decoration: none; word-break: break-all;
            display: block;
        }
        .portal-url-link:hover { color: #c9a84c; text-decoration: underline; }
        /* Checklist */
        .checklist {
            background: #fafafa; border: 1px solid #eee; border-radius: 10px;
            padding: 20px; margin-bottom: 28px; text-align: left;
        }
        .checklist h3 { font-size: 14px; font-weight: 600; color: #1a2d5a; margin-bottom: 14px; }
        .check-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 0; border-bottom: 1px solid #eee;
        }
        .check-item:last-child { border-bottom: none; padding-bottom: 0; }
        .check-icon { font-size: 20px; flex-shrink: 0; }
        .check-text { flex: 1; }
        .check-title { font-size: 14px; font-weight: 600; color: #222; }
        .check-desc { font-size: 12px; color: #888; margin-top: 2px; }
        .check-action a { font-size: 12px; color: #1a2d5a; text-decoration: none; font-weight: 600; }
        .check-action a:hover { text-decoration: underline; }
        /* Buttons */
        .btn-primary {
            display: block; width: 100%; padding: 15px;
            background: #1a2d5a; color: #fff; border: none; border-radius: 8px;
            font-size: 16px; font-weight: 700; cursor: pointer; text-decoration: none;
            transition: background 0.15s; margin-bottom: 12px;
        }
        .btn-primary:hover { background: #142248; }
        .btn-secondary {
            display: block; width: 100%; padding: 13px;
            background: #fff; color: #1a2d5a;
            border: 2px solid #1a2d5a; border-radius: 8px;
            font-size: 15px; font-weight: 600; cursor: pointer; text-decoration: none;
            transition: background 0.15s; margin-bottom: 8px;
        }
        .btn-secondary:hover { background: #eef2ff; }
        .invite-note {
            background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;
            padding: 12px 16px; font-size: 13px; color: #92400e;
            text-align: left; margin-top: 12px; line-height: 1.5;
        }
        .invite-note strong { color: #78350f; }
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
        <div class="step-line"></div>
        <div class="step-dot done">✓</div>
        <div class="step-line"></div>
        <div class="step-dot done">✓</div>
        <div class="step-line"></div>
        <div class="step-dot done">✓</div>
    </div>
    <div class="step-label"><strong>All done!</strong> Your portal is ready.</div>

    <div class="card">
        <div class="celebration">🎉</div>
        <h1>Welcome to QuickShul!</h1>
        <p class="subtitle">
            Your portal for <strong>{{ $tenant->name }}</strong> is live and ready to use.
            Here's what you've set up:
        </p>

        <!-- Portal URL -->
        <div class="portal-url-box">
            <div class="portal-url-label">Your Portal URL</div>
            <a href="{{ $tenant->portalUrl() }}" target="_blank" class="portal-url-link">
                {{ $tenant->portalUrl() }}
            </a>
        </div>

        <!-- Checklist -->
        <div class="checklist">
            <h3>Setup Checklist</h3>

            <div class="check-item">
                <span class="check-icon">✅</span>
                <div class="check-text">
                    <div class="check-title">Portal Created</div>
                    <div class="check-desc">{{ $tenant->name }} — {{ $tenant->slug }}.quickshul.com</div>
                </div>
            </div>

            <div class="check-item">
                @if($tenant->isGmailConnected())
                    <span class="check-icon">✅</span>
                    <div class="check-text">
                        <div class="check-title">Gmail Connected</div>
                        <div class="check-desc">Sending from {{ $tenant->gmail_email }}</div>
                    </div>
                @else
                    <span class="check-icon">⚠️</span>
                    <div class="check-text">
                        <div class="check-title">Gmail Not Connected</div>
                        <div class="check-desc">Emails will use the default mail server</div>
                    </div>
                    <div class="check-action">
                        <a href="{{ route('register.step2') }}">Connect</a>
                    </div>
                @endif
            </div>

            <div class="check-item">
                @if($tenant->isPayPalConnected())
                    <span class="check-icon">✅</span>
                    <div class="check-text">
                        <div class="check-title">PayPal Connected</div>
                        <div class="check-desc">Members can pay online ({{ strtoupper($tenant->paypal_mode ?? 'live') }} mode)</div>
                    </div>
                @else
                    <span class="check-icon">⚠️</span>
                    <div class="check-text">
                        <div class="check-title">PayPal Not Connected</div>
                        <div class="check-desc">Members cannot pay online yet</div>
                    </div>
                    <div class="check-action">
                        <a href="{{ route('register.step3') }}">Connect</a>
                    </div>
                @endif
            </div>
        </div>

        <!-- CTA -->
        <a href="{{ $tenant->portalUrl() }}/admin/members" class="btn-primary">
            Go to Admin Dashboard &rarr;
        </a>

        <div class="invite-note">
            <strong>Next step:</strong> Invite your members! From the admin dashboard, go to
            <em>Emails &rarr; Portal Announcement</em> to send your members their portal invitation.
        </div>
    </div>
</div>
</body>
</html>
