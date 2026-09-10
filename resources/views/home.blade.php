@extends('layouts.app')

@section('title', 'Pump Endless – Launch, Trade, Moon')

@section('content')

<!-- Hero Section matching home.png -->
<section class="hero reveal">
    <div class="hero-grid">
        <div class="hero-content">
            <h1 class="hero-title">
                Launch. Trade.<br>
                <span class="highlight">Moon.</span>
            </h1>
            <p class="hero-subtitle">
                The best place to launch and trade meme coins. 100% fair launch, bonding curve mechanics, and instant liquidity.
            </p>
            <div class="hero-actions">
                <a href="{{ route('coins.launch') }}" class="btn btn-primary btn-lg">
                    Launch a Coin
                </a>
                <a href="#how-it-works" class="btn btn-secondary btn-lg">
                    How it works
                </a>
            </div>
        </div>

        <div class="hero-visual">
            <img src="{{ asset('images/hero-mascot.png') }}" alt="Pepe Astronaut Rocket" class="hero-mascot-img">
        </div>
    </div>
</section>

<!-- <i class="fa-solid fa-fire"></i> Trending Section matching home.png -->
<section class="section reveal">
    <div class="section-header">
        <h2 class="section-title">
            <span><i class="fa-solid fa-fire"></i></span>
            <span>Trending</span>
        </h2>
        <a href="#all-coins" class="btn btn-secondary btn-sm" style="font-size: 0.8rem;">View All</a>
    </div>

    <div class="coins-grid">
        @foreach($trendingCoins as $coin)
            <a href="{{ route('coins.show', $coin->ticker) }}" class="coin-card" data-ticker="{{ $coin->ticker }}">
                <div class="coin-card-header">
                    <img src="{{ $coin->logo_url }}" alt="{{ $coin->name }}" class="coin-avatar" onerror="this.src='{{ asset('images/coins/pepeking.svg') }}'">
                    <div class="coin-meta">
                        <div class="coin-name">{{ $coin->name }}</div>
                        <div class="coin-ticker">{{ $coin->ticker }}</div>
                    </div>
                    <span style="color: var(--text-muted); font-size: 0.85rem;" title="View details">ⓘ</span>
                </div>

                <div class="coin-card-price">
                    <span class="coin-price" data-live-price="{{ $coin->ticker }}">{{ $coin->formatted_price }}</span>
                    <span class="badge-change {{ $coin->change_24h >= 0 ? 'up' : 'down' }}" data-live-change="{{ $coin->ticker }}">
                        {{ $coin->change_24h >= 0 ? '+' : '' }}{{ number_format($coin->change_24h, 2) }}%
                    </span>
                </div>

                <div class="coin-card-stats">
                    <div class="stat-col">
                        <span class="stat-label">24H</span>
                        <span class="stat-value" data-live-volume="{{ $coin->ticker }}">{{ $coin->formatted_volume }}</span>
                    </div>
                    <div class="stat-col">
                        <span class="stat-label">MCAP</span>
                        <span class="stat-value" data-live-mcap="{{ $coin->ticker }}">{{ $coin->formatted_market_cap }}</span>
                    </div>
                    <div class="stat-col">
                        <span class="stat-label">HOLDERS</span>
                        <span class="stat-value" data-live-holders="{{ $coin->ticker }}">{{ $coin->formatted_holders }}</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</section>

<!-- <i class="fa-solid fa-sparkles"></i> New Launches Section matching home.png -->
<section class="section reveal" style="padding-top: 10px;">
    <div class="section-header">
        <h2 class="section-title">
            <span><i class="fa-solid fa-sparkles"></i></span>
            <span>New Launches</span>
        </h2>
        <a href="#all-coins" class="btn btn-secondary btn-sm" style="font-size: 0.8rem;">View All</a>
    </div>

    <div class="coins-grid">
        @foreach($newLaunches as $coin)
            <a href="{{ route('coins.show', $coin->ticker) }}" class="coin-card" data-ticker="{{ $coin->ticker }}">
                <div class="coin-card-header">
                    <img src="{{ $coin->logo_url }}" alt="{{ $coin->name }}" class="coin-avatar" onerror="this.src='{{ asset('images/coins/pepeking.svg') }}'">
                    <div class="coin-meta">
                        <div class="coin-name">{{ $coin->name }}</div>
                        <div class="coin-ticker">{{ $coin->ticker }}</div>
                    </div>
                    <span style="color: var(--text-muted); font-size: 0.85rem;">⏱</span>
                </div>

                <div class="coin-card-price">
                    <span class="coin-price" data-live-price="{{ $coin->ticker }}">{{ $coin->formatted_price }}</span>
                    <span class="badge-change {{ $coin->change_24h >= 0 ? 'up' : 'down' }}" data-live-change="{{ $coin->ticker }}">
                        {{ $coin->change_24h >= 0 ? '+' : '' }}{{ number_format($coin->change_24h, 2) }}%
                    </span>
                </div>

                <div class="coin-card-stats">
                    <div class="stat-col">
                        <span class="stat-label">24H</span>
                        <span class="stat-value" data-live-volume="{{ $coin->ticker }}">{{ $coin->formatted_volume }}</span>
                    </div>
                    <div class="stat-col">
                        <span class="stat-label">MCAP</span>
                        <span class="stat-value" data-live-mcap="{{ $coin->ticker }}">{{ $coin->formatted_market_cap }}</span>
                    </div>
                    <div class="stat-col">
                        <span class="stat-label">HOLDERS</span>
                        <span class="stat-value" data-live-holders="{{ $coin->ticker }}">{{ $coin->formatted_holders }}</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</section>

<!-- Top Coins / Leaderboard -->
<section id="all-coins" class="section reveal">
    <div class="section-header" style="flex-wrap: wrap; gap: 14px;">
        <h2 class="section-title">
            <span><i class="fa-solid fa-bolt"></i></span>
            <span>Top Coins</span>
        </h2>
        <div style="display: flex; align-items: center; gap: 18px; flex-wrap: wrap;">
            <div style="color: var(--text-muted); font-size: 0.85rem;">
                MCap <strong style="color: var(--text-primary); font-family: var(--font-mono);" id="totalMarketCap">${{ number_format($totalMarketCap, 0) }}</strong>
                <span style="color: var(--text-muted); margin: 0 6px;">•</span>
                24h Vol <strong style="color: var(--text-primary); font-family: var(--font-mono);" id="totalVolume">${{ number_format($total24hVolume, 0) }}</strong>
            </div>
            <a href="{{ route('coins.index') }}" class="btn btn-primary btn-sm">
                View All {{ $totalCoins }} Coins <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="coin-leaderboard">
        @foreach($allCoins as $i => $coin)
            <a href="{{ route('coins.show', $coin->ticker) }}" class="coin-leaderboard-row" data-ticker="{{ $coin->ticker }}">
                <span class="coin-rank">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                <img src="{{ $coin->logo_url }}" alt="{{ $coin->name }}" class="coin-leaderboard-img" onerror="this.src='{{ asset('images/coins/pepeking.svg') }}'">
                <span class="coin-leaderboard-name">
                    <span class="coin-leaderboard-title">{{ $coin->name }}</span>
                    <span class="coin-leaderboard-ticker">{{ $coin->ticker }}</span>
                </span>
                <span class="coin-leaderboard-stat" style="display: none;"></span>
                <span class="coin-leaderboard-price" data-live-price="{{ $coin->ticker }}">{{ $coin->formatted_price }}</span>
                <span class="badge-change {{ $coin->change_24h >= 0 ? 'up' : 'down' }}" data-live-change="{{ $coin->ticker }}">
                    {{ $coin->change_24h >= 0 ? '+' : '' }}{{ number_format($coin->change_24h, 2) }}%
                </span>
                <span class="coin-leaderboard-mcap" data-live-mcap="{{ $coin->ticker }}">{{ $coin->formatted_market_cap }}</span>
                <span class="coin-leaderboard-arrow"><i class="fa-solid fa-chevron-right"></i></span>
            </a>
        @endforeach
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <a href="{{ route('coins.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-layer-group"></i> Show All {{ $totalCoins }} Coins
        </a>
    </div>
</section>

<!-- How It Works Section -->
<section id="how-it-works" class="section reveal" style="padding-top: 10px; margin-bottom: 40px;">
    <div class="section-header">
        <h2 class="section-title">
            <span><i class="fa-solid fa-lightbulb"></i></span>
            <span>How Pump Endless Works</span>
        </h2>
    </div>

    <div class="how-it-works-grid">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px;">
            <div style="font-size: 2.2rem; margin-bottom: 12px;"><i class="fa-solid fa-rocket"></i></div>
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 8px;">1. Launch Instant Coins</h3>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                Create any meme coin in 10 seconds. Set the name, ticker, and upload a badge. It launches instantly on our high-speed bonding curve.
            </p>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px;">
            <div style="font-size: 2.2rem; margin-bottom: 12px;"><i class="fa-solid fa-chart-line"></i></div>
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 8px;">2. Buy, Sell & Moon</h3>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                Trade SOL for tokens. As buying pressure mounts, the price automatically moves up and market cap surges with realistic candlestick charts.
            </p>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px;">
            <div style="font-size: 2.2rem; margin-bottom: 12px;"><i class="fa-solid fa-bitcoin-sign"></i></div>
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 8px;">3. Swap to Bitcoin & Withdraw</h3>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                Lock in your meme coin profits by swapping directly to Bitcoin inside your wallet, then submit withdrawal requests.
            </p>
        </div>
    </div>
</section>

<!-- Live Buy Notification Toasts -->
<div id="toastStack" class="toast-stack" aria-live="polite"></div>

@endsection

@push('scripts')
<script>
    (function () {
        const LIVE_URL = '{{ route('api.market.live') }}';
        const toastStack = document.getElementById('toastStack');

        const TRADER_NAMES = [
            'MoonDegen', 'WhaleWatcher', 'CryptoPapi', 'DiamondHands', 'ShibaSlayer',
            'RocketRider', 'ApeLord', 'PumpKing', 'HODLQueen', 'GasFeeGuy',
            'MemeMagician', 'SolSurfer', 'DegenDan', 'LamboSoon', 'FrogFrenzy',
            'TendieTrader', 'BearMarketBen', 'BullRider', 'TokenTornado', 'ZeroToHero'
        ];

        const ICONS = ['<i class="fa-solid fa-frog"></i>', '<i class="fa-solid fa-dog"></i>', '<i class="fa-solid fa-cat"></i>', '<i class="fa-solid fa-otter"></i>', '<i class="fa-solid fa-rocket"></i>', '<i class="fa-solid fa-dragon"></i>', '<i class="fa-solid fa-dove"></i>', '<i class="fa-solid fa-crown"></i>', '<i class="fa-solid fa-bolt"></i>', '<i class="fa-solid fa-fish"></i>'];

        let coins = [];
        let solUsd = 142.5;
        let lastPrices = {};

        // --- Live market updates -------------------------------------------

        function formatPriceChange(pct) {
            return (pct >= 0 ? '+' : '') + Number(pct).toFixed(2) + '%';
        }

        function setBadgeState(el, pct) {
            el.classList.remove('up', 'down');
            el.classList.add(pct >= 0 ? 'up' : 'down');
            el.textContent = formatPriceChange(pct);
        }

        function applyUpdate(coin) {
            const ticker = coin.ticker;

            document.querySelectorAll('[data-live-price="' + ticker + '"]').forEach(el => {
                if (lastPrices[ticker] !== undefined && coin.price !== lastPrices[ticker]) {
                    el.classList.remove('flash-up', 'flash-down');
                    void el.offsetWidth;
                    el.classList.add(coin.price > lastPrices[ticker] ? 'flash-up' : 'flash-down');
                }
                el.textContent = coin.formatted_price;
            });

            document.querySelectorAll('[data-live-change="' + ticker + '"]').forEach(el => setBadgeState(el, coin.change_24h));
            document.querySelectorAll('[data-live-mcap="' + ticker + '"]').forEach(el => (el.textContent = coin.formatted_market_cap));
            document.querySelectorAll('[data-live-volume="' + ticker + '"]').forEach(el => (el.textContent = coin.formatted_volume));
            document.querySelectorAll('[data-live-holders="' + ticker + '"]').forEach(el => (el.textContent = coin.formatted_holders));

            lastPrices[ticker] = coin.price;
        }

        async function pollLive() {
            try {
                const res = await fetch(LIVE_URL, { cache: 'no-store' });
                if (!res.ok) return;
                const data = await res.json();

                coins = data.coins;
                solUsd = data.sol_usd_price;

                coins.forEach(applyUpdate);

                const mcapEl = document.getElementById('totalMarketCap');
                const volEl = document.getElementById('totalVolume');
                if (mcapEl) mcapEl.textContent = '$' + Number(data.total_market_cap).toLocaleString(undefined, { maximumFractionDigits: 0 });
                if (volEl) volEl.textContent = '$' + Number(data.total_volume_24h).toLocaleString(undefined, { maximumFractionDigits: 0 });
            } catch (err) {
                // silent: next poll will retry
            }
        }

        setInterval(pollLive, 4000);
        pollLive();

        // --- Buy notification toasts ----------------------------------------

        function randomOf(arr) {
            return arr[Math.floor(Math.random() * arr.length)];
        }

        function randomBetween(min, max) {
            return min + Math.random() * (max - min);
        }

        function formatTokens(value) {
            if (value >= 1e9) return (value / 1e9).toFixed(2) + 'B';
            if (value >= 1e6) return (value / 1e6).toFixed(2) + 'M';
            if (value >= 1e3) return (value / 1e3).toFixed(1) + 'K';
            return Math.floor(value).toLocaleString();
        }

        function createBuyToast() {
            if (!coins.length) return;

            const coin = randomOf(coins);
            const name = randomOf(TRADER_NAMES);
            const solAmount = Number(randomBetween(0.5, 500).toFixed(2));
            const usdAmount = solAmount * solUsd;
            const tokenAmount = coin.price > 0 ? usdAmount / coin.price : 0;
            const wallet = '0x' + Array.from({ length: 6 }, () => Math.floor(Math.random() * 16).toString(16)).join('');

            const toast = document.createElement('div');
            toast.className = 'buy-toast';
            toast.innerHTML = `
                <div class="buy-toast-icon">${randomOf(ICONS)}</div>
                <div class="buy-toast-body">
                    <div class="buy-toast-title">
                        <span class="buy-trader">${escapeHtml(name)}</span>
                        <span style="color: var(--text-muted); font-size: 0.8rem;">(${wallet})</span>
                    </div>
                    <div class="buy-toast-sub">
                        bought <strong style="color: var(--accent-green);">${formatTokens(tokenAmount)} ${escapeHtml(coin.ticker)}</strong>
                        for <strong style="color: #14f195;">${solAmount} SOL</strong>
                        <span style="color: var(--text-muted); font-size: 0.75rem;">• just now</span>
                    </div>
                </div>
                <button type="button" class="buy-toast-close" title="Dismiss"><i class="fa-solid fa-xmark"></i></button>
            `;

            toast.querySelector('.buy-toast-close').addEventListener('click', () => toast.remove());

            while (toastStack.children.length >= 4) {
                toastStack.firstElementChild.remove();
            }
            toastStack.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('buy-toast-leaving');
                setTimeout(() => toast.remove(), 400);
            }, 6000);
        }

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value == null ? '' : String(value);
            return div.innerHTML;
        }

        function scheduleBuyToast(first) {
            setTimeout(() => {
                createBuyToast();
                scheduleBuyToast(false);
            }, first ? 4000 : randomBetween(7000, 16000));
        }

        scheduleBuyToast(true);
    })();
</script>
@endpush
