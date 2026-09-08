<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard – Pump Endless')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body style="background: #090c13;">

    <div class="admin-wrapper">
        <!-- Sidebar matching admin dashboard.png -->
        <aside class="admin-sidebar">
            <div style="display: flex; align-items: center; gap: 10px; padding: 0 12px;">
                <div class="brand-icon">🚀</div>
                <div style="font-weight: 800; font-size: 1.3rem;">PUMPY</div>
            </div>

            <ul class="admin-menu">
                <li class="admin-menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <span>📊</span>
                        <span>Overview</span>
                    </a>
                </li>
                <li class="admin-menu-item {{ request()->is('admin/coins*') ? 'active' : '' }}">
                    <a href="{{ route('admin.coins.index') }}">
                        <span>🪙</span>
                        <span>Coins</span>
                    </a>
                </li>
                <li class="admin-menu-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}">
                        <span>👥</span>
                        <span>Users</span>
                    </a>
                </li>
                <li class="admin-menu-item {{ request()->is('admin/deposits*') ? 'active' : '' }}">
                    <a href="{{ route('admin.deposits.index') }}">
                        <span>📥</span>
                        <span>Deposits</span>
                    </a>
                </li>
                <li class="admin-menu-item {{ request()->is('admin/withdrawals*') ? 'active' : '' }}">
                    <a href="{{ route('admin.withdrawals.index') }}">
                        <span>📤</span>
                        <span>Withdrawals</span>
                    </a>
                </li>
                <li class="admin-menu-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
                    <a href="{{ route('admin.settings.index') }}">
                        <span>⚙️</span>
                        <span>Settings & Fees</span>
                    </a>
                </li>
            </ul>

            <div style="margin-top: auto; border-top: 1px solid var(--border-color); padding-top: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0 12px; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.5rem;">👑</span>
                        <div>
                            <div style="font-weight: 700; font-size: 0.9rem;">Admin</div>
                            <div style="font-size: 0.75rem; color: var(--accent-green);">Super Admin</div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('home') }}" class="btn btn-secondary btn-sm" style="flex: 1; font-size: 0.8rem;" target="_blank">
                        View Site ↗
                    </a>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" title="Log Out">✕</button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="admin-main">
            <!-- Admin Top Bar -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
                <div>
                    <h1 style="font-size: 1.8rem; font-weight: 800;">@yield('header_title', 'Admin Dashboard')</h1>
                    <div style="color: var(--text-muted); font-size: 0.85rem;">Platform Simulation & Market Controls</div>
                </div>

                <div style="display: flex; align-items: center; gap: 14px;">
                    <a href="{{ route('admin.coins.create') }}" class="btn btn-primary btn-sm">
                        + New Coin
                    </a>
                </div>
            </div>

            <!-- Flash Alerts -->
            @if(session('success'))
                <div class="alert alert-success">
                    <span>✓ {{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:inherit; cursor:pointer;">✕</button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">
                    <span>⚠ {{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:inherit; cursor:pointer;">✕</button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
