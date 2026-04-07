<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') &mdash; {{ $tenant->name ?? config('app.name') }} Member Portal</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="apple-touch-icon" sizes="192x192" href="/favicon-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy: #1a2d5a;
            --navy-dark: #111d3c;
            --gold: #c9a84c;
            --cream: #faf8f4;
            --text: #2c2c2c;
            --text-light: #6b6b6b;
            --border: #ddd6c8;
            --white: #ffffff;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--cream);
            color: var(--text);
            line-height: 1.7;
        }

        /* Header */
        .site-header {
            background: var(--navy);
            padding: 0.9rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .site-header a {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            text-decoration: none;
        }

        .header-logo {
            width: 42px;
            height: 42px;
            object-fit: contain;
            border-radius: 50%;
            background: var(--white);
            padding: 2px;
            border: 2px solid var(--gold);
        }

        .header-brand-text {
            display: flex;
            flex-direction: column;
        }

        .header-name {
            font-family: 'Playfair Display', serif;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--white);
            line-height: 1.2;
        }

        .header-sub {
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--gold);
        }

        /* Main layout */
        .page-wrap {
            max-width: 820px;
            margin: 0 auto;
            padding: 3rem 1.5rem 5rem;
        }

        /* Document header */
        .doc-header {
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--gold);
        }

        .doc-label {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 0.5rem;
        }

        .doc-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.2;
            margin-bottom: 0.6rem;
        }

        .doc-meta {
            font-size: 0.82rem;
            color: var(--text-light);
        }

        /* Content */
        .doc-body h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--navy);
            margin-top: 2.25rem;
            margin-bottom: 0.6rem;
        }

        .doc-body p {
            font-size: 0.92rem;
            margin-bottom: 0.9rem;
            color: var(--text);
        }

        .doc-body ul, .doc-body ol {
            font-size: 0.92rem;
            padding-left: 1.4rem;
            margin-bottom: 0.9rem;
        }

        .doc-body li {
            margin-bottom: 0.4rem;
        }

        .doc-body a {
            color: var(--navy);
            text-decoration: underline;
        }

        .doc-body a:hover {
            color: var(--gold);
        }

        .doc-body strong {
            font-weight: 600;
            color: var(--navy-dark);
        }

        .callout {
            background: var(--white);
            border-left: 3px solid var(--gold);
            border-radius: 0 8px 8px 0;
            padding: 0.9rem 1.1rem;
            margin: 1.25rem 0;
            font-size: 0.88rem;
        }

        /* Footer nav */
        .legal-footer {
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
            display: flex;
            flex-wrap: wrap;
            gap: 1rem 2rem;
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .legal-footer a {
            color: var(--navy);
            text-decoration: none;
            font-weight: 500;
        }

        .legal-footer a:hover {
            color: var(--gold);
        }

        @media (max-width: 600px) {
            .site-header { padding: 0.75rem 1rem; }
            .doc-title { font-size: 1.5rem; }
            .page-wrap { padding: 2rem 1rem 4rem; }
        }
    </style>
</head>
<body>

    <header class="site-header">
        <a href="/login">
            @if($tenant->logo_url ?? null)<img class="header-logo" src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}">@endif
            <div class="header-brand-text">
                <span class="header-name">{{ $tenant->name ?? config('app.name') }}</span>
                <span class="header-sub">Member Portal</span>
            </div>
        </a>
    </header>

    <div class="page-wrap">

        <div class="doc-header">
            <div class="doc-label">@yield('doc-label')</div>
            <h1 class="doc-title">@yield('title')</h1>
            <p class="doc-meta">@yield('meta')</p>
        </div>

        <div class="doc-body">
            @yield('content')
        </div>

        <div class="legal-footer">
            <span>&copy; {{ date('Y') }} {{ $tenant->name ?? config('app.name') }}. All rights reserved.</span>
            <a href="/login">&larr; Back to Login</a>
            <a href="/agreement">End User License Agreement</a>
            <a href="/privacy">Privacy Policy</a>
        </div>

    </div>

</body>
</html>
