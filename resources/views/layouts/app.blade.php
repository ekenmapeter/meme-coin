<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pump Endless – Demo Meme Coin Launch & Trading Platform')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/coins/pepeking.svg') }}">
    <!-- Chart.js for Candlestick and Line charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luxon@3.4.4/build/global/luxon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1.3.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-chart-financial@0.1.1/dist/chartjs-chart-financial.min.js"></script>
    <!-- QRCode.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>

    <!-- Header / Navbar -->
    <header class="header">
        <div class="header-container">
            <a href="{{ route('home') }}" class="brand">
                <div class="brand-icon">🚀</div>
                <span>PUMPY</span>
            </a>

            <ul class="nav-links">
                <li><a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
                <li><a href="{{ route('coins.launch') }}" class="nav-link {{ request()->routeIs('coins.launch') ? 'active' : '' }}">Launch</a></li>
                <li><a href="{{ route('wallet.index', ['tab' => 'swap']) }}" class="nav-link {{ request()->is('swap*') || (request()->routeIs('wallet.index') && request('tab') == 'swap') ? 'active' : '' }}">Swap</a></li>
                <li><a href="{{ route('wallet.index') }}" class="nav-link {{ request()->routeIs('wallet.index') && request('tab') != 'swap' ? 'active' : '' }}">Wallet</a></li>
                @if(Auth::check() && Auth::user()->isAdmin())
                    <li><a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->is('admin*') ? 'active' : '' }}">Dashboard</a></li>
                @endif
            </ul>

            <!-- Search input with auto-suggest -->
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" id="globalSearchInput" class="search-input" placeholder="Search coins..." autocomplete="off">
                <div id="searchDropdown" class="search-dropdown"></div>
            </div>

            <div class="user-nav-actions">
                @if(Auth::check())
                    <a href="{{ route('wallet.index') }}" class="btn btn-secondary btn-sm" style="font-family: var(--font-mono); font-size: 0.85rem; border-color: rgba(0,240,118,0.3);">
                        <span style="color: var(--accent-green); font-size: 0.9rem;">●</span>
                        <span>{{ substr(Auth::user()->wallet_address ?? '0x8f...a1b2', 0, 6) }}...{{ substr(Auth::user()->wallet_address ?? '0x8f...a1b2', -4) }}</span>
                        <span style="color: #10b981; font-weight: 700; margin-left: 4px;">{{ number_format(Auth::user()->sol_balance, 2) }} SOL</span>
                    </a>
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-sm" style="padding: 6px 12px; font-size: 0.8rem;">
                            Admin
                        </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" title="Disconnect" style="padding: 6px 10px;">✕</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-secondary btn-sm">
                        Register
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Global Flash Alerts -->
    <div style="max-width: 1440px; margin: 16px auto 0; padding: 0 24px; width: 100%;">
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
    </div>

    <!-- Main Content -->
    <main style="flex: 1;">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div style="max-width: 1440px; margin: 0 auto; display: flex; flex-direction: column; gap: 12px; align-items: center;">
            <div style="display: flex; gap: 20px; align-items: center; font-weight: 600;">
                <a href="{{ route('home') }}" style="color: var(--text-primary);">Pump Endless</a>
                <span>•</span>
                <a href="{{ route('wallet.index') }}">Wallet & Swap</a>
                <span>•</span>
                <a href="{{ route('coins.launch') }}">Launch Coin</a>
                @if(Auth::check() && Auth::user()->isAdmin())
                    <span>•</span>
                    <a href="{{ route('admin.dashboard') }}">Admin Panel</a>
                @endif
            </div>
            <p style="color: var(--text-muted); font-size: 0.8rem; max-width: 650px;">
                Pump Endless is a high-speed demo simulation platform. All prices, trading activity, charts, and market caps are simulated for demonstration purposes.
            </p>
        </div>
    </footer>

    <!-- Global Javascript -->
    <script>
        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value == null ? '' : String(value);
            return div.innerHTML;
        }

        // Live Search
        const searchInput = document.getElementById('globalSearchInput');
        const searchDropdown = document.getElementById('searchDropdown');
        let searchTimeout;

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const q = this.value.trim();
                if (q.length < 1) {
                    searchDropdown.classList.remove('active');
                    return;
                }
                searchTimeout = setTimeout(async () => {
                    const res = await fetch(`{{ route('search') }}?q=${encodeURIComponent(q)}`);
                    const coins = await res.json();
                    if (coins.length > 0) {
                        searchDropdown.innerHTML = coins.map(c => `
                            <a href="/coins/${escapeHtml(c.ticker)}" class="search-item">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <img src="/images/coins/${escapeHtml(c.logo_path)}" style="width:28px; height:28px; border-radius:50%;" onerror="this.src='/images/coins/pepeking.svg'">
                                    <div>
                                        <span style="font-weight:700;">${escapeHtml(c.name)}</span>
                                        <span style="color:var(--text-muted); font-size:0.8rem; margin-left:4px;">${escapeHtml(c.ticker)}</span>
                                    </div>
                                </div>
                                <div style="text-align:right;">
                                    <div style="font-family:var(--font-mono); font-weight:700;">$${Number(c.current_price).toFixed(6)}</div>
                                    <div style="font-size:0.75rem; color:${Number(c.change_24h) >= 0 ? 'var(--accent-green)' : 'var(--accent-red)'}">
                                        ${Number(c.change_24h) >= 0 ? '+' : ''}${Number(c.change_24h).toFixed(2)}%
                                    </div>
                                </div>
                            </a>
                        `).join('');
                        searchDropdown.classList.add('active');
                    } else {
                        searchDropdown.innerHTML = '<div style="padding:14px; color:var(--text-muted); text-align:center; font-size:0.9rem;">No coins found</div>';
                        searchDropdown.classList.add('active');
                    }
                }, 200);
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                    searchDropdown.classList.remove('active');
                }
            });
        }
    </script>
    @stack('scripts')
</body>
</html>