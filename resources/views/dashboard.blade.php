<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard &mdash; Young Israel of Memphis Member Portal</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="apple-touch-icon" sizes="192x192" href="/favicon-192.png">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --navy: #1a2d5a; --gold: #c9a84c; --cream: #faf8f4; }
        body { font-family: 'Inter', sans-serif; background: var(--cream); min-height: 100vh; }
        header {
            background: var(--navy);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header-brand { color: #fff; font-family: 'Playfair Display', serif; font-size: 1.1rem; }
        .header-brand span { color: var(--gold); }
        .header-user { display: flex; align-items: center; gap: 1rem; }
        .header-user p { color: rgba(255,255,255,0.8); font-size: 0.875rem; }
        .btn-logout {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.3);
            color: #fff;
            padding: 0.4rem 0.9rem;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
        }
        .btn-logout:hover { background: rgba(255,255,255,0.1); }
        main { max-width: 900px; margin: 3rem auto; padding: 0 1.5rem; }
        h1 { font-family: 'Playfair Display', serif; color: var(--navy); font-size: 1.75rem; margin-bottom: 0.5rem; }
        p.welcome { color: #6b6b6b; }
    </style>
</head>
<body>
    <header>
        <div class="header-brand">Young Israel of Memphis &mdash; <span>Member Portal</span></div>
        <div class="header-user">
            <p>{{ auth()->user()->name }}</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">Sign Out</button>
            </form>
        </div>
    </header>
    <main>
        <h1>Welcome, {{ auth()->user()->name }}</h1>
        <p class="welcome">Your member dashboard is coming soon.</p>
    </main>
</body>
</html>
