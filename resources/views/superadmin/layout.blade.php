<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Super Admin') &mdash; QuickShul</title>
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
            --bg:         #0f1a2e;
            --bg-card:    #162240;
            --bg-card2:   #1a2a4a;
            --sidebar:    #0d1828;
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

        /* Top bar */
        .sa-topbar {
            background: var(--navy-dark);
            border-bottom: 2px solid var(--gold);
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            flex-shrink: 0;
        }

        .topbar-brand { display: flex; align-items: center; gap: 0.75rem; text-decoration: none; }
        .topbar-logo-text {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--gold);
            letter-spacing: 0.03em;
        }
        .topbar-subtitle {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: rgba(255,255,255,0.5);
            margin-left: 0.5rem;
            padding: 0.2rem 0.5rem;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 4px;
        }

        .topbar-right { display: flex; align-items: center; gap: 1rem; font-size: 0.8rem; color: var(--text-muted); }
        .btn-logout { background: transparent; border: 1px solid rgba(255,255,255,0.15); color: rgba(255,255,255,0.6); padding: 0.3rem 0.7rem; border-radius: 6px; font-size: 0.75rem; font-family: 'Inter', sans-serif; cursor: pointer; }
        .btn-logout:hover { border-color: var(--gold); color: var(--gold); }

        /* Layout */
        .sa-body { display: flex; flex: 1; }

        /* Sidebar */
        .sa-sidebar {
            width: 200px;
            flex-shrink: 0;
            background: var(--sidebar);
            border-right: 1px solid var(--border-dim);
            padding: 1.5rem 0;
        }

        .sidebar-label { font-size: 0.62rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-muted); padding: 0 1.25rem; margin-bottom: 0.4rem; }
        .sidebar-link {
            display: flex; align-items: center; gap: 0.6rem;
            padding: 0.5rem 1.25rem; text-decoration: none;
            font-size: 0.85rem; font-weight: 500; color: rgba(255,255,255,0.6);
            border-left: 3px solid transparent; transition: all 0.15s;
        }
        .sidebar-link:hover { color: #fff; background: rgba(255,255,255,0.04); }
        .sidebar-link.active { color: var(--gold); border-left-color: var(--gold); background: rgba(201,168,76,0.06); }

        /* Main content */
        .sa-main { flex: 1; overflow-y: auto; padding: 2rem 2rem 4rem; }

        /* Cards & tables */
        .card { background: var(--bg-card); border: 1px solid var(--border-dim); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.25rem; }
        .card-title { font-family: 'Playfair Display', serif; font-size: 1.05rem; font-weight: 600; color: var(--gold); margin-bottom: 1rem; padding-bottom: 0.6rem; border-bottom: 1px solid var(--border); }
        .page-title { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 0.3rem; }
        .page-subtitle { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem; }

        .alert { padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.875rem; margin-bottom: 1.25rem; border-left: 3px solid; }
        .alert-success { background: rgba(46,204,113,0.1); border-color: var(--success); color: #6fe8a2; }
        .alert-error   { background: rgba(231,76,60,0.1);  border-color: var(--error);   color: #f08080; }
        .alert-info    { background: rgba(52,152,219,0.1); border-color: #3498db;         color: #7ec8f5; }

        .table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        .table th { text-align: left; padding: 0.6rem 0.875rem; color: var(--gold); font-weight: 600; font-size: 0.72rem; letter-spacing: 0.05em; text-transform: uppercase; border-bottom: 1px solid var(--border); white-space: nowrap; }
        .table td { padding: 0.65rem 0.875rem; border-bottom: 1px solid var(--border-dim); color: var(--text); vertical-align: middle; }
        .table tr:last-child td { border-bottom: none; }
        .table tr:hover td { background: rgba(255,255,255,0.02); }

        .btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.4rem 0.85rem; border-radius: 7px; font-size: 0.8rem; font-weight: 500; font-family: 'Inter', sans-serif; cursor: pointer; text-decoration: none; border: none; transition: all 0.15s; }
        .btn-primary { background: linear-gradient(135deg, var(--navy-mid), var(--navy-dark)); color: #fff; border: 1px solid rgba(201,168,76,0.3); }
        .btn-primary:hover { border-color: var(--gold); }
        .btn-gold { background: linear-gradient(135deg, var(--gold), #a8882a); color: var(--navy-dark); font-weight: 600; }
        .btn-gold:hover { filter: brightness(1.1); }
        .btn-outline { background: transparent; border: 1px solid var(--border-dim); color: var(--text-muted); }
        .btn-outline:hover { border-color: var(--gold); color: var(--gold); }
        .btn-danger { background: rgba(231,76,60,0.15); color: #f08080; border: 1px solid rgba(231,76,60,0.3); }
        .btn-danger:hover { background: rgba(231,76,60,0.25); }
        .btn-sm { padding: 0.25rem 0.55rem; font-size: 0.72rem; }
        .btn-warning { background: rgba(243,156,18,0.15); color: #f5c76b; border: 1px solid rgba(243,156,18,0.3); }
        .btn-warning:hover { background: rgba(243,156,18,0.25); }

        .badge { display: inline-block; padding: 0.2rem 0.55rem; border-radius: 20px; font-size: 0.68rem; font-weight: 600; letter-spacing: 0.04em; }
        .badge-green   { background: rgba(46,204,113,0.15);  color: #6fe8a2; }
        .badge-yellow  { background: rgba(243,156,18,0.15);  color: #f5c76b; }
        .badge-red     { background: rgba(231,76,60,0.15);   color: #f08080; }
        .badge-muted   { background: rgba(255,255,255,0.06); color: var(--text-muted); }

        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-bottom: 1.5rem; }
        @media (max-width: 800px) { .grid-3 { grid-template-columns: 1fr; } }
        .stat-card { background: var(--bg-card2); border: 1px solid var(--border-dim); border-radius: 10px; padding: 1rem 1.25rem; }
        .stat-label { font-size: 0.7rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.35rem; }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: #fff; font-family: 'Playfair Display', serif; line-height: 1; }
        .stat-value.gold { color: var(--gold); }

        .text-muted { color: var(--text-muted); }
        .text-gold  { color: var(--gold); }
        .text-sm    { font-size: 0.8rem; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 0.5rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        .divider { height: 1px; background: var(--border-dim); margin: 1.5rem 0; }

        .pagination { display: flex; gap: 0.3rem; align-items: center; justify-content: center; flex-wrap: wrap; margin-top: 1.5rem; }
        .pagination ul { display: flex; gap: 0.3rem; align-items: center; list-style: none; margin: 0; padding: 0; }
        .pagination li { display: inline-flex; }
        .pagination a, .pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; padding: 0; border-radius: 6px; font-size: 0.8rem; text-decoration: none; color: var(--text-muted); border: 1px solid var(--border-dim); transition: all 0.15s; }
        .pagination a { cursor: pointer; }
        .pagination a:hover { border-color: var(--gold); color: var(--gold); }
        .pagination .active span { background: var(--navy-mid); color: var(--gold); border-color: var(--gold); }
    </style>
    @yield('styles')
</head>
<body>
    <div class="sa-topbar">
        <a class="topbar-brand" href="{{ route('superadmin.index') }}">
            <span class="topbar-logo-text">QuickShul</span>
            <span class="topbar-subtitle">Super Admin</span>
        </a>
        <div class="topbar-right">
            <span>{{ auth()->user()->name ?? '' }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">Sign Out</button>
            </form>
        </div>
    </div>

    <div class="sa-body">
        <aside class="sa-sidebar">
            <div style="margin-bottom:1.5rem">
                <div class="sidebar-label" style="margin-bottom:0.5rem">Navigation</div>
                <a href="{{ route('superadmin.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.index') || request()->routeIs('superadmin.tenants.*') ? 'active' : '' }}">
                    🏛 Tenants
                </a>
            </div>
        </aside>

        <main class="sa-main">
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
        </main>
    </div>

    @yield('scripts')
</body>
</html>
