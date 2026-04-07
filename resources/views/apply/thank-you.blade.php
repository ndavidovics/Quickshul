<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Received &mdash; {{ $tenant->name ?? config('app.name') }}</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --navy:#1a2d5a; --navy-dark:#111d3c; --gold:#c9a84c; --cream:#faf8f4; --text:#2c2c2c; --text-light:#6b6b6b; --border:#ddd6c8; --white:#ffffff; }
        body { min-height: 100vh; background-color: var(--cream); background-image: radial-gradient(ellipse at 20% 50%, rgba(26,45,90,0.06) 0%, transparent 60%), radial-gradient(ellipse at 80% 20%, rgba(201,168,76,0.08) 0%, transparent 50%); font-family: 'Inter', sans-serif; color: var(--text); display: flex; align-items: center; justify-content: center; padding: 2rem 1rem; }
        .page-wrapper { width: 100%; max-width: 520px; text-align: center; }
        .logo-container { display: inline-block; background: var(--white); border-radius: 50%; padding: 10px; box-shadow: 0 4px 20px rgba(26,45,90,0.12); margin-bottom: 1.25rem; border: 3px solid var(--gold); }
        .logo-container img { width: 72px; height: 72px; object-fit: contain; display: block; border-radius: 50%; }
        .card { background: var(--white); border-radius: 16px; padding: 2.5rem 2rem; box-shadow: 0 4px 6px rgba(26,45,90,0.04), 0 10px 40px rgba(26,45,90,0.08); border: 1px solid rgba(201,168,76,0.15); }
        .checkmark { font-size: 3rem; margin-bottom: 1rem; }
        h1 { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--navy); margin-bottom: 0.75rem; }
        p { font-size: 0.9rem; color: var(--text-light); line-height: 1.65; margin-bottom: 0.75rem; }
        .btn { display: inline-block; margin-top: 1.25rem; padding: 0.75rem 1.75rem; background: linear-gradient(135deg, var(--navy) 0%, var(--navy-dark) 100%); color: white; border-radius: 10px; font-size: 0.9rem; font-weight: 600; text-decoration: none; font-family: 'Inter', sans-serif; transition: all 0.2s; }
        .btn:hover { box-shadow: 0 4px 15px rgba(26,45,90,0.35); transform: translateY(-1px); }
        .divider { height: 1px; background: var(--border); margin: 1.25rem 0; }
        .contact { font-size: 0.8rem; color: var(--text-light); }
        .contact a { color: var(--navy); text-decoration: none; }
        .contact a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="logo-container">
        <img src="{{ $tenant->logo_url ?? asset('img/quickshul-logo.svg') }}" alt="{{ $tenant->name ?? config('app.name') }}">
    </div>

    <div class="card">
        <div class="checkmark">&#10003;</div>
        <h1>Application Received!</h1>
        <p>Thank you for applying to become a member of {{ $tenant->name ?? config('app.name') }}. We're excited to welcome you to our community.</p>
        <p>Our office will review your application and be in touch with you shortly. If you have any questions in the meantime, please don't hesitate to reach out.</p>
        <div class="divider"></div>
        <p class="contact">Questions? Contact us at <a href="mailto:{{ $tenant->org_email ?? '' }}">{{ $tenant->org_email ?? '' }}</a></p>
        <a href="{{ route('login') }}" class="btn">Return to Login</a>
    </div>
</div>
</body>
</html>
