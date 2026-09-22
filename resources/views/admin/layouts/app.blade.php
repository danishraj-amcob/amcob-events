<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') — AMCOB Events</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --navy: #1e2d5a; --gold: #c89b3c; --bg: #f4f5f8; --card: #fff;
            --line: #e4e6ed; --t1: #111827; --t2: #6b7280; --radius: 10px;
            --red: #dc2626; --green: #16a34a;
        }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--t1); font-size: 14px; min-height: 100vh; display: flex; }

        /* Sidebar */
        .sidebar { width: 220px; background: var(--navy); flex-shrink: 0; display: flex; flex-direction: column; min-height: 100vh; position: sticky; top: 0; height: 100vh; }
        .sidebar-brand { padding: 22px 20px 18px; border-bottom: 1px solid rgba(255,255,255,.1); }
        .sidebar-brand span { font-size: 13px; font-weight: 700; color: #fff; letter-spacing: .5px; }
        .sidebar-brand small { display: block; font-size: 10.5px; color: rgba(255,255,255,.45); font-weight: 500; margin-top: 2px; }
        .sidebar-nav { padding: 14px 0; flex: 1; }
        .sidebar-nav a { display: flex; align-items: center; gap: 10px; padding: 10px 20px; color: rgba(255,255,255,.65); font-size: 13px; font-weight: 500; text-decoration: none; transition: .18s; }
        .sidebar-nav a:hover, .sidebar-nav a.active { color: #fff; background: rgba(255,255,255,.08); }
        .sidebar-nav a svg { width: 16px; height: 16px; flex-shrink: 0; }
        .sidebar-nav .divider { height: 1px; background: rgba(255,255,255,.08); margin: 8px 20px; }
        .sidebar-footer { padding: 14px 20px; border-top: 1px solid rgba(255,255,255,.1); }
        .sidebar-footer form button { background: none; border: none; color: rgba(255,255,255,.55); font-size: 12.5px; font-weight: 500; cursor: pointer; padding: 0; font-family: inherit; }
        .sidebar-footer form button:hover { color: #fff; }

        /* Main */
        .main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .topbar { background: var(--card); border-bottom: 1px solid var(--line); padding: 14px 28px; display: flex; align-items: center; justify-content: space-between; }
        .topbar h1 { font-size: 16px; font-weight: 700; }
        .topbar .user { font-size: 13px; color: var(--t2); }
        .content { padding: 28px; flex: 1; }

        /* Cards */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .stat-card { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); padding: 20px; }
        .stat-card .label { font-size: 12px; font-weight: 600; color: var(--t2); text-transform: uppercase; letter-spacing: .5px; }
        .stat-card .value { font-size: 32px; font-weight: 800; color: var(--navy); margin-top: 6px; line-height: 1; }
        .stat-card.confirmed .value { color: var(--green); }
        .stat-card.failed .value { color: var(--red); }

        /* Table */
        .card { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); overflow: hidden; }
        .card-head { padding: 16px 20px; border-bottom: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; }
        .card-head h2 { font-size: 14px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; }
        th { font-size: 11.5px; font-weight: 700; color: var(--t2); text-transform: uppercase; letter-spacing: .4px; padding: 11px 16px; text-align: left; border-bottom: 1px solid var(--line); background: #fafafa; white-space: nowrap; }
        td { padding: 12px 16px; border-bottom: 1px solid var(--line); font-size: 13px; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafbff; }
        .badge { display: inline-block; padding: 3px 9px; border-radius: 99px; font-size: 11px; font-weight: 700; }
        .badge.confirmed { background: #dcfce7; color: var(--green); }
        .badge.failed { background: #fee2e2; color: var(--red); }
        .badge.pending { background: #fef9c3; color: #92400e; }
        .mono { font-family: monospace; font-size: 12px; color: var(--t2); }
        .t2 { color: var(--t2); }
        .pagination { display: flex; align-items: center; justify-content: flex-end; gap: 6px; padding: 14px 20px; border-top: 1px solid var(--line); }
        .pagination a, .pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; padding: 0 8px; border-radius: 6px; font-size: 13px; font-weight: 500; text-decoration: none; color: var(--t2); border: 1px solid var(--line); background: var(--card); }
        .pagination span.active { background: var(--navy); color: #fff; border-color: var(--navy); }
        .pagination a:hover { background: var(--bg); }

        /* Alerts */
        .alert { padding: 12px 16px; border-radius: var(--radius); margin-bottom: 20px; font-size: 13px; font-weight: 500; }
        .alert-danger { background: #fee2e2; color: #991b1b; }

        /* Login */
        .login-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--bg); width: 100%; }
        .login-box { background: var(--card); border: 1px solid var(--line); border-radius: 14px; padding: 40px 36px; width: 100%; max-width: 380px; }
        .login-box .brand { text-align: center; margin-bottom: 28px; }
        .login-box .brand span { font-size: 15px; font-weight: 800; color: var(--navy); }
        .login-box .brand small { display: block; font-size: 12px; color: var(--t2); margin-top: 3px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 12.5px; font-weight: 600; margin-bottom: 6px; color: var(--t1); }
        .form-group input { width: 100%; padding: 10px 13px; border: 1px solid var(--line); border-radius: 8px; font-family: inherit; font-size: 14px; color: var(--t1); outline: none; transition: .18s; }
        .form-group input:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(30,45,90,.1); }
        .btn-primary { width: 100%; padding: 11px; background: var(--navy); color: #fff; border: none; border-radius: 8px; font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; margin-top: 6px; transition: .18s; }
        .btn-primary:hover { background: #162247; }
        .empty { text-align: center; padding: 48px 20px; color: var(--t2); font-size: 13px; }
    </style>
</head>
<body style="{{ Auth::guard('admin')->check() ? '' : 'display:block' }}">
    @auth('admin')
    <nav class="sidebar">
        <div class="sidebar-brand">
            <span>AMCOB Events</span>
            <small>Admin Panel</small>
        </div>
        <div class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>
            <a href="{{ route('admin.registrations.index') }}" class="{{ request()->routeIs('admin.registrations.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Registrations
            </a>
            <div class="divider"></div>
            <a href="{{ route('home') }}" target="_blank">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                View site
            </a>
        </div>
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit">Sign out — {{ Auth::guard('admin')->user()->name }}</button>
            </form>
        </div>
    </nav>
    @endauth

    <div class="main">
        @auth('admin')
        <div class="topbar">
            <h1>@yield('title', 'Dashboard')</h1>
            <span class="user">{{ Auth::guard('admin')->user()->email }}</span>
        </div>
        @endauth

        <div class="content">
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error) {{ $error }}<br> @endforeach
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</body>
</html>
