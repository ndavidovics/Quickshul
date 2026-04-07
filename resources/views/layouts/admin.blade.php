<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') &mdash; {{ $tenant->name ?? config('app.name') }} Admin</title>
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
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Top bar ── */
        .admin-topbar {
            background: var(--navy-dark);
            border-bottom: 1px solid var(--border);
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            flex-shrink: 0;
        }

        .topbar-brand { display: flex; align-items: center; gap: 0.6rem; text-decoration: none; }
        .topbar-logo  { width: 32px; height: 32px; object-fit: contain; border-radius: 50%; border: 2px solid var(--gold); background: #fff; padding: 1px; }
        .topbar-logo-letter { width: 32px; height: 32px; border-radius: 50%; border: 2px solid var(--gold); background: var(--gold); color: #1a2d5a; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; font-weight: 800; font-family: 'Playfair Display', serif; flex-shrink: 0; }
        .topbar-title { font-family: 'Playfair Display', serif; font-size: 0.9rem; font-weight: 700; color: #fff; }
        .topbar-badge { font-size: 0.6rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; background: rgba(201,168,76,0.2); color: var(--gold); padding: 0.15rem 0.5rem; border-radius: 4px; margin-left: 0.4rem; }

        .topbar-right { display: flex; align-items: center; gap: 0.75rem; font-size: 0.8rem; color: var(--text-muted); }
        .btn-topbar-member { color: var(--gold); text-decoration: none; font-size: 0.78rem; }
        .btn-topbar-member:hover { text-decoration: underline; }
        .btn-logout { background: transparent; border: 1px solid rgba(255,255,255,0.15); color: rgba(255,255,255,0.6); padding: 0.3rem 0.7rem; border-radius: 6px; font-size: 0.75rem; font-family: 'Inter', sans-serif; cursor: pointer; }
        .btn-logout:hover { border-color: var(--gold); color: var(--gold); }

        /* ── Layout ── */
        .admin-body { display: flex; flex: 1; overflow: hidden; }

        /* ── Sidebar ── */
        .admin-sidebar {
            width: 220px;
            flex-shrink: 0;
            background: var(--sidebar);
            border-right: 1px solid var(--border-dim);
            padding: 1.5rem 0;
            display: flex;
            flex-direction: column;
        }

        .sidebar-section { margin-bottom: 1.5rem; }
        .sidebar-label { font-size: 0.62rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-muted); padding: 0 1.25rem; margin-bottom: 0.4rem; }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.5rem 1.25rem;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            color: rgba(255,255,255,0.6);
            border-left: 3px solid transparent;
            transition: all 0.15s;
        }
        .sidebar-link:hover { color: #fff; background: rgba(255,255,255,0.04); }
        .sidebar-link.active { color: var(--gold); border-left-color: var(--gold); background: rgba(201,168,76,0.06); }
        .sidebar-icon { width: 16px; text-align: center; font-size: 0.85rem; }

        /* ── Main content ── */
        .admin-main { flex: 1; overflow-y: auto; padding: 2rem 2rem 4rem; }

        /* ── Hamburger (hidden on desktop) ── */
        .hamburger {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.3rem;
            color: var(--text);
            flex-direction: column;
            gap: 5px;
            margin-right: 0.5rem;
        }
        .hamburger span {
            display: block;
            width: 22px;
            height: 2px;
            background: currentColor;
            border-radius: 2px;
            transition: all 0.2s;
        }

        /* ── Sidebar overlay backdrop ── */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 99;
        }
        .sidebar-backdrop.open { display: block; }

        /* ── Mobile styles ── */
        @media (max-width: 768px) {
            .hamburger { display: flex; }

            .admin-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                z-index: 100;
                transform: translateX(-100%);
                transition: transform 0.25s ease;
                padding-top: 1rem;
                width: 240px;
            }
            .admin-sidebar.open { transform: translateX(0); }

            .admin-main { padding: 1.25rem 1rem 4rem; }

            .topbar-right .topbar-name { display: none; }

            .admin-topbar { padding: 0 1rem; }
        }

        /* ── Reuse member styles ── */
        .card { background: var(--bg-card); border: 1px solid var(--border-dim); border-radius: 12px; padding: 1.5rem; }
        .card + .card { margin-top: 1.25rem; }
        .card-title { font-family: 'Playfair Display', serif; font-size: 1.05rem; font-weight: 600; color: var(--gold); margin-bottom: 1rem; padding-bottom: 0.6rem; border-bottom: 1px solid var(--border); }
        .page-title { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 0.3rem; }
        .page-subtitle { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem; }
        .alert { padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.875rem; margin-bottom: 1.25rem; border-left: 3px solid; }
        .alert-success { background: rgba(46,204,113,0.1); border-color: var(--success); color: #6fe8a2; }
        .alert-error   { background: rgba(231,76,60,0.1);  border-color: var(--error);   color: #f08080; }
        .alert-info    { background: rgba(52,152,219,0.1); border-color: #3498db;         color: #7ec8f5; }
        .alert-warning { background: rgba(243,156,18,0.1); border-color: var(--warning, #f39c12); color: #f5c76b; }
        .table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        .table th { text-align: left; padding: 0.6rem 0.875rem; color: var(--gold); font-weight: 600; font-size: 0.72rem; letter-spacing: 0.05em; text-transform: uppercase; border-bottom: 1px solid var(--border); white-space: nowrap; }
        .table td { padding: 0.65rem 0.875rem; border-bottom: 1px solid var(--border-dim); color: var(--text); vertical-align: middle; }
        .table tr:last-child td { border-bottom: none; }
        .table tr:hover td { background: rgba(255,255,255,0.02); }
        .btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem; font-weight: 500; font-family: 'Inter', sans-serif; cursor: pointer; text-decoration: none; border: none; transition: all 0.15s; }
        .btn-primary { background: linear-gradient(135deg, var(--navy-mid), var(--navy-dark)); color: #fff; border: 1px solid rgba(201,168,76,0.3); }
        .btn-primary:hover { border-color: var(--gold); }
        .btn-gold { background: linear-gradient(135deg, var(--gold), #a8882a); color: var(--navy-dark); font-weight: 600; }
        .btn-gold:hover { filter: brightness(1.1); }
        .btn-outline { background: transparent; border: 1px solid var(--border-dim); color: var(--text-muted); }
        .btn-outline:hover { border-color: var(--gold); color: var(--gold); }
        .btn-danger { background: rgba(231,76,60,0.15); color: #f08080; border: 1px solid rgba(231,76,60,0.3); }
        .btn-danger:hover { background: rgba(231,76,60,0.25); }
        .btn-sm { padding: 0.3rem 0.65rem; font-size: 0.75rem; }
        .form-group { margin-bottom: 1.1rem; }
        .form-label { display: block; font-size: 0.72rem; font-weight: 600; color: var(--gold); margin-bottom: 0.35rem; letter-spacing: 0.04em; text-transform: uppercase; }
        .form-control { width: 100%; padding: 0.6rem 0.875rem; background: var(--bg); border: 1px solid var(--border-dim); border-radius: 8px; color: var(--text); font-size: 0.875rem; font-family: 'Inter', sans-serif; transition: border-color 0.15s; }
        .form-control:focus { outline: none; border-color: var(--gold); box-shadow: 0 0 0 2px rgba(201,168,76,0.15); }
        .form-control::placeholder { color: var(--text-muted); }
        select.form-control option { background: var(--bg-card); }
        .badge { display: inline-block; padding: 0.2rem 0.55rem; border-radius: 20px; font-size: 0.68rem; font-weight: 600; letter-spacing: 0.04em; }
        .badge-gold  { background: rgba(201,168,76,0.15);  color: var(--gold); }
        .badge-green { background: rgba(46,204,113,0.15);  color: #6fe8a2; }
        .badge-red   { background: rgba(231,76,60,0.15);   color: #f08080; }
        .badge-blue  { background: rgba(52,152,219,0.15);  color: #7ec8f5; }
        .badge-muted { background: rgba(255,255,255,0.06); color: var(--text-muted); }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem; }
        @media (max-width: 800px) { .grid-2, .grid-3 { grid-template-columns: 1fr; } }
        .stat-card { background: var(--bg-card2); border: 1px solid var(--border-dim); border-radius: 10px; padding: 1rem 1.25rem; }
        .stat-label { font-size: 0.7rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.35rem; }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: #fff; font-family: 'Playfair Display', serif; line-height: 1; }
        .stat-value.gold { color: var(--gold); }
        .divider { height: 1px; background: var(--border-dim); margin: 1.5rem 0; }
        .text-muted { color: var(--text-muted); }
        .text-gold  { color: var(--gold); }
        .text-sm    { font-size: 0.8rem; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 0.5rem; }
        .gap-3 { gap: 0.75rem; }
        .pagination { display: flex; gap: 0.3rem; align-items: center; justify-content: center; flex-wrap: wrap; margin-top: 1.5rem; }
        .pagination ul { display: flex; gap: 0.3rem; align-items: center; list-style: none; margin: 0; padding: 0; }
        .pagination li { display: inline-flex; }
        .pagination a, .pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; padding: 0; border-radius: 6px; font-size: 0.8rem; text-decoration: none; color: var(--text-muted); border: 1px solid var(--border-dim); transition: all 0.15s; }
        .pagination a { cursor: pointer; }
        .pagination a:hover { border-color: var(--gold); color: var(--gold); background: rgba(201,168,76,0.06); }
        .pagination .active span { background: var(--navy-mid); color: var(--gold); border-color: var(--gold); }
        .pagination li.disabled span { opacity: 0.5; cursor: not-allowed; }
    </style>
    @yield('styles')
</head>
<body>
    <div class="admin-topbar">
        <div style="display:flex;align-items:center">
            <button class="hamburger" id="sidebar-toggle" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
            <a class="topbar-brand" href="{{ route('admin.members') }}">
                @if($tenant->logo_url ?? null)
                    <img class="topbar-logo" src="{{ $tenant->logo_url }}" alt="{{ $tenant->name ?? config('app.name') }}">
                @else
                    <div class="topbar-logo-letter">{{ strtoupper(mb_substr($tenant->name ?? config('app.name'), 0, 1)) }}</div>
                @endif
                <span class="topbar-title">{{ $tenant->name ?? config('app.name') }}</span>
                <span class="topbar-badge">Admin</span>
            </a>
        </div>
        <div class="topbar-right">
            <a href="{{ route('dashboard') }}" class="btn-topbar-member">← Member View</a>
            <span class="topbar-name">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">Sign Out</button>
            </form>
        </div>
    </div>

    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    <div class="admin-body">
        <aside class="admin-sidebar" id="admin-sidebar">
            <div class="sidebar-section">
                <div class="sidebar-label">Members</div>
                <a href="{{ route('admin.members') }}" class="sidebar-link {{ request()->routeIs('admin.members*') ? 'active' : '' }}">
                    <span class="sidebar-icon">👥</span> All Members
                </a>
                <a href="{{ route('admin.yahrtzeits.index') }}" class="sidebar-link {{ request()->routeIs('admin.yahrtzeits*') ? 'active' : '' }}">
                    <span class="sidebar-icon">🕯</span> Yahrtzeits
                </a>
                <a href="{{ route('admin.financials.payments') }}" class="sidebar-link {{ request()->routeIs('admin.financials.payments*') ? 'active' : '' }}">
                    <span class="sidebar-icon">💳</span> Payments
                </a>
                <a href="{{ route('admin.financials.pledges') }}" class="sidebar-link {{ request()->routeIs('admin.financials.pledges*') ? 'active' : '' }}">
                    <span class="sidebar-icon">📄</span> Pledges
                </a>
            </div>
            @if($tenant->qb_enabled ?? false)
            <div class="sidebar-section">
                <div class="sidebar-label">Integrations</div>
                <a href="{{ route('admin.qb') }}" class="sidebar-link {{ request()->routeIs('admin.qb*') ? 'active' : '' }}">
                    <span class="sidebar-icon">📊</span> QuickBooks Sync
                </a>
            </div>
            @endif
            <div class="sidebar-section">
                <div class="sidebar-label">Communications</div>
                <a href="{{ route('admin.emails') }}" class="sidebar-link {{ request()->routeIs('admin.emails*') ? 'active' : '' }}">
                    <span class="sidebar-icon">✉️</span> Email Reminders
                </a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-label">Calendar</div>
                <a href="{{ route('admin.calendar.generate') }}" class="sidebar-link {{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}">
                    <span class="sidebar-icon">📅</span> Calendar Builder
                </a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-label">System</div>
                <a href="{{ route('admin.applications.index') }}" class="sidebar-link {{ request()->routeIs('admin.applications*') ? 'active' : '' }}">
                    <span class="sidebar-icon">📋</span> Applications
                    @php $pendingCount = \App\Models\MemberApplication::where('status','pending')->count(); @endphp
                    @if($pendingCount > 0)
                        <span style="margin-left:auto;background:var(--gold);color:var(--navy-dark);border-radius:10px;padding:0.1rem 0.45rem;font-size:0.65rem;font-weight:700">{{ $pendingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.users') }}" class="sidebar-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <span class="sidebar-icon">🔑</span> User Management
                </a>
                <a href="{{ route('admin.settings') }}" class="sidebar-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                    <span class="sidebar-icon">⚙️</span> Settings
                </a>
            </div>
        </aside>

        <main class="admin-main">
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
<script>
(function () {
    var toggle   = document.getElementById('sidebar-toggle');
    var sidebar  = document.getElementById('admin-sidebar');
    var backdrop = document.getElementById('sidebar-backdrop');

    function open()  { sidebar.classList.add('open'); backdrop.classList.add('open'); }
    function close() { sidebar.classList.remove('open'); backdrop.classList.remove('open'); }

    toggle.addEventListener('click', function () {
        sidebar.classList.contains('open') ? close() : open();
    });
    backdrop.addEventListener('click', close);

    // Close on nav link tap (mobile UX)
    sidebar.querySelectorAll('.sidebar-link').forEach(function (link) {
        link.addEventListener('click', close);
    });
})();
</script>
</body>
</html>
