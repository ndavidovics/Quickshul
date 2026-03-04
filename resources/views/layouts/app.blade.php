<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Member Portal') &mdash; Young Israel of Memphis</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:       #1a2d5a;
            --navy-dark:  #111d3c;
            --navy-mid:   #1e3366;
            --gold:       #c9a84c;
            --gold-light: #e0c06a;
            --cream:      #faf8f4;
            --bg:         #0f1a2e;
            --bg-card:    #162240;
            --bg-card2:   #1a2a4a;
            --text:       #e8e4dc;
            --text-muted: #8899bb;
            --border:     rgba(201,168,76,0.2);
            --border-dim: rgba(255,255,255,0.08);
            --success:    #2ecc71;
            --error:      #e74c3c;
            --warning:    #f39c12;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Header ── */
        .site-header {
            background: var(--navy-dark);
            border-bottom: 1px solid var(--border);
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
            flex-shrink: 0;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .header-logo {
            width: 38px;
            height: 38px;
            object-fit: contain;
            border-radius: 50%;
            border: 2px solid var(--gold);
            background: #fff;
            padding: 2px;
        }

        .brand-text { display: flex; flex-direction: column; line-height: 1.2; }
        .brand-name { font-family: 'Playfair Display', serif; font-size: 0.95rem; font-weight: 700; color: #fff; }
        .brand-sub  { font-size: 0.65rem; font-weight: 500; letter-spacing: 0.1em; text-transform: uppercase; color: var(--gold); }

        /* ── Nav ── */
        .header-nav {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .nav-link {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 0.4rem 0.75rem;
            border-radius: 6px;
            transition: all 0.15s;
        }

        .nav-link:hover, .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.08);
        }

        .nav-link.active { color: var(--gold); }

        .header-right { display: flex; align-items: center; gap: 0.75rem; }

        .user-chip {
            font-size: 0.8rem;
            color: var(--text-muted);
            display: none;
        }

        @media (min-width: 640px) { .user-chip { display: block; } }

        .btn-logout {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.2);
            color: rgba(255,255,255,0.7);
            padding: 0.35rem 0.8rem;
            border-radius: 6px;
            font-size: 0.78rem;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-logout:hover { border-color: var(--gold); color: var(--gold); }

        /* ── Page wrapper ── */
        .page-content {
            flex: 1;
            max-width: 1100px;
            width: 100%;
            margin: 0 auto;
            padding: 2rem 1.5rem 4rem;
        }

        /* ── Cards ── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-dim);
            border-radius: 12px;
            padding: 1.5rem;
        }

        .card + .card { margin-top: 1.25rem; }

        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--gold);
            margin-bottom: 1rem;
            padding-bottom: 0.6rem;
            border-bottom: 1px solid var(--border);
        }

        /* ── Page title ── */
        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.35rem;
        }

        .page-subtitle {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 1.75rem;
        }

        /* ── Alerts ── */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
            border-left: 3px solid;
        }
        .alert-success { background: rgba(46,204,113,0.1); border-color: var(--success); color: #6fe8a2; }
        .alert-error   { background: rgba(231,76,60,0.1);  border-color: var(--error);   color: #f08080; }
        .alert-info    { background: rgba(52,152,219,0.1); border-color: #3498db;         color: #7ec8f5; }

        /* ── Table ── */
        .table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        .table th { text-align: left; padding: 0.6rem 0.875rem; color: var(--gold); font-weight: 600; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase; border-bottom: 1px solid var(--border); }
        .table td { padding: 0.7rem 0.875rem; border-bottom: 1px solid var(--border-dim); color: var(--text); vertical-align: middle; }
        .table tr:last-child td { border-bottom: none; }
        .table tr:hover td { background: rgba(255,255,255,0.03); }

        /* ── Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.15s;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--navy-mid), var(--navy-dark));
            color: #fff;
            border: 1px solid rgba(201,168,76,0.3);
        }
        .btn-primary:hover { border-color: var(--gold); box-shadow: 0 0 12px rgba(201,168,76,0.2); }

        .btn-gold {
            background: linear-gradient(135deg, var(--gold), #a8882a);
            color: var(--navy-dark);
            font-weight: 600;
        }
        .btn-gold:hover { filter: brightness(1.1); }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-dim);
            color: var(--text-muted);
        }
        .btn-outline:hover { border-color: var(--gold); color: var(--gold); }

        .btn-danger { background: rgba(231,76,60,0.15); color: #f08080; border: 1px solid rgba(231,76,60,0.3); }
        .btn-danger:hover { background: rgba(231,76,60,0.25); }

        .btn-sm { padding: 0.3rem 0.65rem; font-size: 0.78rem; }

        /* ── Form ── */
        .form-group { margin-bottom: 1.1rem; }
        .form-label { display: block; font-size: 0.78rem; font-weight: 600; color: var(--gold); margin-bottom: 0.35rem; letter-spacing: 0.04em; text-transform: uppercase; }
        .form-control {
            width: 100%;
            padding: 0.6rem 0.875rem;
            background: var(--bg);
            border: 1px solid var(--border-dim);
            border-radius: 8px;
            color: var(--text);
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.15s;
        }
        .form-control:focus { outline: none; border-color: var(--gold); box-shadow: 0 0 0 2px rgba(201,168,76,0.15); }
        .form-control::placeholder { color: var(--text-muted); }
        select.form-control option { background: var(--bg-card); }

        /* ── Badge ── */
        .badge {
            display: inline-block;
            padding: 0.2rem 0.55rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.04em;
        }
        .badge-gold    { background: rgba(201,168,76,0.15);  color: var(--gold); }
        .badge-green   { background: rgba(46,204,113,0.15);  color: #6fe8a2; }
        .badge-red     { background: rgba(231,76,60,0.15);   color: #f08080; }
        .badge-blue    { background: rgba(52,152,219,0.15);  color: #7ec8f5; }
        .badge-muted   { background: rgba(255,255,255,0.06); color: var(--text-muted); }

        /* ── Grid helpers ── */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem; }
        @media (max-width: 700px) { .grid-2, .grid-3 { grid-template-columns: 1fr; } }

        /* ── Stats ── */
        .stat-card {
            background: var(--bg-card2);
            border: 1px solid var(--border-dim);
            border-radius: 10px;
            padding: 1.1rem 1.25rem;
        }
        .stat-label { font-size: 0.72rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.4rem; }
        .stat-value { font-size: 1.6rem; font-weight: 700; color: #fff; font-family: 'Playfair Display', serif; line-height: 1; }
        .stat-value.gold { color: var(--gold); }
        .stat-sub   { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.3rem; }

        /* ── Pagination ── */
        .pagination { display: flex; gap: 0.4rem; flex-wrap: wrap; margin-top: 1.25rem; }
        .pagination a, .pagination span {
            padding: 0.35rem 0.7rem;
            border-radius: 6px;
            font-size: 0.8rem;
            text-decoration: none;
            color: var(--text-muted);
            border: 1px solid var(--border-dim);
        }
        .pagination .active span { background: var(--navy-mid); color: var(--gold); border-color: var(--gold); }
        .pagination a:hover { border-color: var(--gold); color: var(--gold); }

        /* ── Misc ── */
        .divider { height: 1px; background: var(--border-dim); margin: 1.5rem 0; }
        .text-muted { color: var(--text-muted); }
        .text-gold  { color: var(--gold); }
        .text-sm    { font-size: 0.8rem; }
        .mt-1 { margin-top: 0.5rem; }
        .mt-2 { margin-top: 1rem; }
        .mt-3 { margin-top: 1.5rem; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 0.5rem; }
        .gap-3 { gap: 0.75rem; }
    </style>
    @yield('styles')
</head>
<body>
    <header class="site-header">
        <a class="header-brand" href="{{ route('dashboard') }}">
            <img class="header-logo"
                 src="https://static.wixstatic.com/media/e7610d_01030d0ca83f445b8c033c146a1ee4fb~mv2.png"
                 alt="YIOM">
            <div class="brand-text">
                <span class="brand-name">Young Israel of Memphis</span>
                <span class="brand-sub">Member Portal</span>
            </div>
        </a>

        <nav class="header-nav">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('family') }}"    class="nav-link {{ request()->routeIs('family')    ? 'active' : '' }}">My Family</a>
            <a href="{{ route('financial') }}" class="nav-link {{ request()->routeIs('financial*') ? 'active' : '' }}">Financial</a>
            <a href="{{ route('settings') }}"  class="nav-link {{ request()->routeIs('settings*')  ? 'active' : '' }}">Settings</a>
            @if(auth()->user()->is_admin)
                <a href="{{ route('admin.members') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">Admin</a>
            @endif
        </nav>

        <div class="header-right">
            <span class="user-chip">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">Sign Out</button>
            </form>
        </div>
    </header>

    <div class="page-content">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </div>

    @yield('scripts')
</body>
</html>
