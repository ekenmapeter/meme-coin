@php
    $siteName = \App\Models\PlatformSetting::get('site_name', 'Pump Endless');
    $siteDescription = \App\Models\PlatformSetting::get('site_description', 'Pump Endless – a high-speed demo meme-coin launch & trading platform. All prices and activity are simulated.');
    $siteLogo = \App\Models\PlatformSetting::get('site_logo');
    $siteIcon = \App\Models\PlatformSetting::get('site_icon', 'images/coins/pepeking.svg');
    $user = Auth::user();

    $headerPortfolioUsd = 0.0;
    if ($user) {
        $headerPrices = app(\App\Services\CryptoPriceService::class);
        $headerBtcPriceUsd = $headerPrices->btcUsd();
        $headerSolPriceUsd = $headerPrices->solUsd();
        $headerHoldingsUsd = \App\Models\UserHolding::with('coin')
            ->where('user_id', $user->id)
            ->where('token_balance', '>', 0)
            ->get()
            ->sum(fn ($h) => $h->token_balance * ($h->coin->current_price ?? 0));
        $headerPortfolioUsd = $headerHoldingsUsd + $user->usd_balance + ($user->btc_balance * $headerBtcPriceUsd) + ($user->sol_balance * $headerSolPriceUsd);
    }
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $siteDescription }}">
    <title>@yield('title', $siteName . ' – Launch, Trade, Moon')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset($siteIcon) }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') . '?v=' . @filemtime(public_path('css/style.css')) }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Chart.js for Candlestick and Line charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luxon@3.4.4/build/global/luxon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1.3.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-chart-financial@0.1.1/dist/chartjs-chart-financial.min.js"></script>
    <!-- QRCode.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body x-data="{ drawerOpen: false, menuOpen: false }">

    <!-- Header / Navbar -->
    <header class="header">
        <div class="header-container">
            <a href="{{ route('home') }}" class="brand">
                @if($siteLogo)
                    <img src="{{ asset($siteLogo) }}" alt="{{ $siteName }}" class="brand-img">
                @else
                    <div class="brand-icon"><i class="fa-solid fa-rocket"></i></div>
                @endif
                <span>{{ $siteName }}</span>
            </a>

            <div class="header-center">
            <ul class="nav-links">
                @if($user)
                    <li><a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a></li>
                @endif
                <li><a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
                <li><a href="{{ route('coins.index') }}" class="nav-link {{ request()->routeIs('coins.index', 'coins.show') ? 'active' : '' }}">Markets</a></li>
                <li><a href="{{ route('coins.launch') }}" class="nav-link {{ request()->routeIs('coins.launch') ? 'active' : '' }}">Launch</a></li>
                <li><a href="{{ route('wallet.index', ['tab' => 'swap']) }}" class="nav-link {{ request()->is('swap*') || (request()->routeIs('wallet.index') && request('tab') == 'swap') ? 'active' : '' }}">Swap</a></li>
                <li><a href="{{ route('wallet.index') }}" class="nav-link {{ request()->routeIs('wallet.index') && request('tab') != 'swap' ? 'active' : '' }}">Wallet</a></li>
                @if($user && $user->isAdmin())
                    <li><a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->is('admin*') ? 'active' : '' }}">Admin</a></li>
                @endif
            </ul>

            <!-- Search input with auto-suggest (hidden on mobile) -->
            <div class="search-box hidden md:flex">
                <span class="search-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="globalSearchInput" class="search-input" placeholder="Search coins..." autocomplete="off">
                <div id="searchDropdown" class="search-dropdown"></div>
            </div>
        </div>

        <div class="user-nav-actions">
                @if($user)
                    <a href="{{ route('wallet.index') }}" class="btn btn-secondary btn-sm" title="Total portfolio value" style="font-family: var(--font-mono); font-size: 0.85rem; border-color: rgba(0,240,118,0.3);">
                        <span style="color: var(--accent-green); font-size: 0.9rem;"><i class="fa-solid fa-circle"></i></span>
                        <span class="hidden lg:inline">{{ substr($user->wallet_address ?? '0x8f...a1b2', 0, 6) }}...{{ substr($user->wallet_address ?? '0x8f...a1b2', -4) }}</span>
                        <span style="color: #10b981; font-weight: 700; margin-left: 4px;">
                            @if($headerPortfolioUsd >= 1000000)
                                ${{ number_format($headerPortfolioUsd / 1000000, 1) }}M
                            @elseif($headerPortfolioUsd >= 1000)
                                ${{ number_format($headerPortfolioUsd / 1000, 1) }}K
                            @else
                                ${{ number_format($headerPortfolioUsd, 2) }}
                            @endif
                        </span>
                    </a>
                    <a href="{{ route('profile.show') }}" class="btn btn-secondary btn-sm" title="Profile" style="padding: 6px 10px;"><i class="fa-solid fa-user"></i></a>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" title="Disconnect" style="padding: 6px 10px;"><i class="fa-solid fa-right-from-bracket"></i></button>
                    </form>
                    @if($user->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-sm" style="padding: 6px 12px; font-size: 0.8rem;">
                            <i class="fa-solid fa-shield-halved"></i> Admin
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
                    <a href="{{ route('register') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-user-plus"></i> Register</a>
                @endif
            </div>
        </div>
    </header>

    <!-- Global Flash Alerts -->
    <div style="max-width: 1440px; margin: 16px auto 0; padding: 0 16px; width: 100%;">
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
    </div>

    <!-- Main Content -->
    <main style="flex: 1; padding-bottom: 84px;">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="max-w-[1440px] mx-auto flex flex-col items-center gap-6 px-1">
            <div class="flex items-center gap-3">
                @if($siteLogo)
                    <img src="{{ asset($siteLogo) }}" alt="{{ $siteName }}" class="brand-img">
                @else
                    <div class="brand-icon"><i class="fa-solid fa-rocket"></i></div>
                @endif
                <span class="text-white font-extrabold text-lg">{{ $siteName }}</span>
            </div>

            <p class="text-slate-400 text-sm max-w-xl text-center leading-relaxed">
                The fastest demo meme-coin launchpad. Launch tokens, trade with simulated SOL,
                and swap to BTC — all in one place.
            </p>

            <div class="flex gap-3">
                <a href="#" class="footer-social" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="#" class="footer-social" aria-label="Telegram"><i class="fa-brands fa-telegram"></i></a>
                <a href="#" class="footer-social" aria-label="Discord"><i class="fa-brands fa-discord"></i></a>
                <a href="#" class="footer-social" aria-label="GitHub"><i class="fa-brands fa-github"></i></a>
            </div>

            <p class="text-slate-500 text-xs flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-amber-400"></i>
                Simulation only. All prices, balances and activity are fictional.
            </p>
        </div>

        <div class="max-w-[1440px] mx-auto mt-8 pt-6 border-t border-white/10 flex flex-col md:flex-row items-center justify-center gap-3 text-xs text-slate-500">
            <span>&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</span>
            <span class="hidden md:inline">•</span>
            <span class="flex items-center gap-2">
                <span class="status-dot" style="background:#22c55e"></span>
                Simulated demo environment
            </span>
        </div>
    </footer>

    @if($user)
    <!-- ===== Mobile Bottom Navigation (signed-in users) ===== -->
    <nav class="bottom-nav" aria-label="Mobile navigation">
        <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
        </a>
        <a href="{{ route('coins.launch') }}" class="bottom-nav-item {{ request()->routeIs('coins.launch') ? 'active' : '' }}">
            <i class="fa-solid fa-bolt"></i><span>Launch</span>
        </a>
        <a href="{{ route('wallet.index', ['tab' => 'swap']) }}" class="bottom-nav-item {{ request()->is('swap*') || (request()->routeIs('wallet.index') && request('tab') == 'swap') ? 'active' : '' }}">
            <i class="fa-solid fa-arrow-right-arrow-left"></i><span>Swap</span>
        </a>
        <a href="{{ route('wallet.index') }}" class="bottom-nav-item {{ request()->routeIs('wallet.index') && request('tab') != 'swap' ? 'active' : '' }}">
            <i class="fa-solid fa-wallet"></i><span>Wallet</span>
        </a>
        <button type="button" class="bottom-nav-item" @click="drawerOpen = true">
            <i class="fa-solid fa-bars-staggered"></i><span>Menu</span>
        </button>
    </nav>

    <!-- ===== Mobile Drawer (Alpine) ===== -->
    <div x-cloak x-show="drawerOpen" x-transition.opacity
         class="fixed inset-0 z-[60] bg-black/60 backdrop-blur-sm md:hidden" @click="drawerOpen = false"></div>
    <aside x-cloak x-show="drawerOpen" x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0"
           x-transition:leave-end="translate-x-full"
           class="fixed right-0 top-0 z-[70] h-full w-[82%] max-w-sm drawer-panel md:hidden">
        <div class="flex items-center justify-between p-5 border-b border-white/10">
            <div class="flex items-center gap-3">
                @if($siteLogo)
                    <img src="{{ asset($siteLogo) }}" alt="{{ $siteName }}" class="h-9 w-9 rounded-full object-cover border border-white/10">
                @else
                    <div class="h-9 w-9 rounded-full bg-accent/20 text-accent grid place-items-center"><i class="fa-solid fa-rocket"></i></div>
                @endif
                <div>
                    <div class="font-bold text-white">{{ $siteName }}</div>
                    <div class="text-xs text-slate-400">Demo Trading Platform</div>
                </div>
            </div>
            <button type="button" class="text-slate-400 hover:text-white text-xl" @click="drawerOpen = false" aria-label="Close menu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-4 border-b border-white/10 bg-white/[0.03]">
            <div class="flex items-center gap-3">
                @if($user->avatar && file_exists(public_path($user->avatar)))
                    <img src="{{ asset($user->avatar) }}" alt="{{ $user->name }}" class="h-10 w-10 rounded-full object-cover border border-white/10">
                @else
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-accent to-emerald-700 grid place-items-center font-bold text-black">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <div class="font-bold text-white truncate">{{ $user->name }}</div>
                    <div class="text-xs text-slate-400 font-mono truncate">{{ $user->email }}</div>
                </div>
            </div>
            <div class="mt-3 flex gap-2 text-xs font-mono">
                <span class="flex-1 rounded-lg bg-black/30 border border-white/10 px-3 py-2 text-emerald-400 font-bold">{{ number_format($user->sol_balance, 2) }} SOL</span>
                <span class="flex-1 rounded-lg bg-black/30 border border-white/10 px-3 py-2 text-amber-400 font-bold">{{ sprintf('%.6f', $user->btc_balance) }} BTC</span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-1">
            <a href="{{ route('dashboard') }}" class="drawer-link"><i class="fa-solid fa-gauge-high w-6 text-accent"></i> Dashboard</a>
            <a href="{{ route('home') }}" class="drawer-link"><i class="fa-solid fa-house w-6 text-accent"></i> Home</a>
            <a href="{{ route('coins.index') }}" class="drawer-link"><i class="fa-solid fa-chart-line w-6 text-accent"></i> Markets</a>
            <a href="{{ route('coins.launch') }}" class="drawer-link"><i class="fa-solid fa-bolt w-6 text-accent"></i> Launch a Coin</a>
            <a href="{{ route('wallet.index', ['tab' => 'swap']) }}" class="drawer-link"><i class="fa-solid fa-arrow-right-arrow-left w-6 text-accent"></i> Swap to BTC</a>
            <a href="{{ route('wallet.index') }}" class="drawer-link"><i class="fa-solid fa-wallet w-6 text-accent"></i> Wallet</a>
            <a href="{{ route('wallet.index', ['tab' => 'deposit']) }}" class="drawer-link"><i class="fa-solid fa-right-to-bracket w-6 text-accent"></i> Deposit</a>
            <a href="{{ route('wallet.index', ['tab' => 'withdraw']) }}" class="drawer-link"><i class="fa-solid fa-right-from-bracket w-6 text-accent"></i> Withdraw</a>
            <a href="{{ route('profile.show') }}" class="drawer-link"><i class="fa-solid fa-user w-6 text-accent"></i> Profile</a>
            @if($user->isAdmin())
                <div class="pt-2 mt-2 border-t border-white/10 text-xs uppercase tracking-widest text-slate-500 font-bold px-3">Admin</div>
                <a href="{{ route('admin.dashboard') }}" class="drawer-link"><i class="fa-solid fa-gauge-high w-6 text-amber-400"></i> Dashboard</a>
                <a href="{{ route('admin.coins.index') }}" class="drawer-link"><i class="fa-solid fa-coins w-6 text-amber-400"></i> Manage Coins</a>
                <a href="{{ route('admin.users.index') }}" class="drawer-link"><i class="fa-solid fa-users w-6 text-amber-400"></i> Manage Users</a>
                <a href="{{ route('admin.settings.index') }}" class="drawer-link"><i class="fa-solid fa-sliders w-6 text-amber-400"></i> Settings</a>
            @endif
        </div>

        <div class="p-4 border-t border-white/10">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full rounded-xl bg-white/5 border border-white/10 py-3 text-sm font-semibold text-white hover:bg-white/10 transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-from-bracket text-danger"></i> Sign Out
                </button>
            </form>
        </div>
    </aside>
    @endif

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
                                    <img src="${escapeHtml(c.logo_url)}" style="width:28px; height:28px; border-radius:50%;" onerror="this.src='/images/coins/pepeking.svg'">
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

        // Scroll reveal
        (function () {
            const els = document.querySelectorAll('.reveal');
            if (!('IntersectionObserver' in window)) {
                els.forEach(el => el.classList.add('revealed'));
                return;
            }
            const io = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
            els.forEach(el => io.observe(el));
        })();
    </script>
    @stack('scripts')
</body>
</html>