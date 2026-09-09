@php
    $siteName = \App\Models\PlatformSetting::get('site_name', 'Pump Endless');
    $siteLogo = \App\Models\PlatformSetting::get('site_logo');
    $siteIcon = \App\Models\PlatformSetting::get('site_icon', 'images/coins/pepeking.svg');
    $admin = Auth::user();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard – ' . $siteName)</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset($siteIcon) }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') . '?v=' . @filemtime(public_path('css/style.css')) }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body style="background: #090c13;">

    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div style="display: flex; align-items: center; gap: 10px; padding: 0 12px;">
                @if($siteLogo)
                    <img src="{{ asset($siteLogo) }}" alt="{{ $siteName }}" style="width: 32px; height: 32px; border-radius: 9px; object-fit: cover;">
                @else
                    <div class="brand-icon"><i class="fa-solid fa-rocket"></i></div>
                @endif
                <div style="font-weight: 800; font-size: 1.2rem; line-height: 1.1;">{{ $siteName }}</div>
            </div>

            <ul class="admin-menu">
                <li class="admin-menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <span><i class="fa-solid fa-gauge-high"></i></span>
                        <span>Overview</span>
                    </a>
                </li>
                <li class="admin-menu-item {{ request()->is('admin/coins*') ? 'active' : '' }}">
                    <a href="{{ route('admin.coins.index') }}">
                        <span><i class="fa-solid fa-coins"></i></span>
                        <span>Coins</span>
                    </a>
                </li>
                <li class="admin-menu-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}">
                        <span><i class="fa-solid fa-users"></i></span>
                        <span>Users</span>
                    </a>
                </li>
                <li class="admin-menu-item {{ request()->is('admin/deposits*') ? 'active' : '' }}">
                    <a href="{{ route('admin.deposits.index') }}">
                        <span><i class="fa-solid fa-arrow-down-to-line"></i></span>
                        <span>Deposits</span>
                    </a>
                </li>
                <li class="admin-menu-item {{ request()->is('admin/withdrawals*') ? 'active' : '' }}">
                    <a href="{{ route('admin.withdrawals.index') }}">
                        <span><i class="fa-solid fa-arrow-up-from-line"></i></span>
                        <span>Withdrawals</span>
                    </a>
                </li>
                <li class="admin-menu-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
                    <a href="{{ route('admin.settings.index') }}">
                        <span><i class="fa-solid fa-sliders"></i></span>
                        <span>Settings & Fees</span>
                    </a>
                </li>
            </ul>

            <div style="margin-top: auto; border-top: 1px solid var(--border-color); padding-top: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0 12px; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.5rem;"><i class="fa-solid fa-crown" style="color: #f59e0b;"></i></span>
                        <div>
                            <div style="font-weight: 700; font-size: 0.9rem;">{{ $admin->name }}</div>
                            <div style="font-size: 0.75rem; color: var(--accent-green);">Super Admin</div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('home') }}" class="btn btn-secondary btn-sm" style="flex: 1; font-size: 0.8rem;" target="_blank">
                        View Site <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" title="Log Out"><i class="fa-solid fa-right-from-bracket"></i></button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="admin-main">
            <!-- Admin Top Bar -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color); flex-wrap: wrap; gap: 12px;">
                <div>
                    <h1 style="font-size: 1.8rem; font-weight: 800;">@yield('header_title', 'Admin Dashboard')</h1>
                    <div style="color: var(--text-muted); font-size: 0.85rem;">Platform Simulation & Market Controls</div>
                </div>

                <div style="display: flex; align-items: center; gap: 14px;">
                    <a href="{{ route('admin.coins.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-plus"></i> New Coin
                    </a>
                </div>
            </div>

            <!-- Flash Alerts -->
            @if(session('success'))
                <div class="alert alert-success">
                    <span><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:inherit; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">
                    <span><i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:inherit; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>