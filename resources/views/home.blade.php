@extends('layouts.app')

@section('title', 'Pump Endless – Launch, Trade, Moon (Demo Meme Coin Platform)')

@section('content')

<!-- Hero Section matching home.png -->
<section class="hero">
    <div class="hero-grid">
        <div class="hero-content">
            <h1 class="hero-title">
                Launch. Trade.<br>
                <span class="highlight">Moon.</span>
            </h1>
            <p class="hero-subtitle">
                The best place to launch and trade meme coins. 100% fair launch, bonding curve mechanics, and instant simulated liquidity.
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

<!-- 🔥 Trending Section matching home.png -->
<section class="section">
    <div class="section-header">
        <h2 class="section-title">
            <span>🔥</span>
            <span>Trending</span>
        </h2>
        <a href="#all-coins" class="btn btn-secondary btn-sm" style="font-size: 0.8rem;">View All</a>
    </div>

    <div class="coins-grid">
        @foreach($trendingCoins as $coin)
            <a href="{{ route('coins.show', $coin->ticker) }}" class="coin-card">
                <div class="coin-card-header">
                    <img src="{{ asset('images/coins/' . $coin->logo_path) }}" alt="{{ $coin->name }}" class="coin-avatar" onerror="this.src='{{ asset('images/coins/pepeking.svg') }}'">
                    <div class="coin-meta">
                        <div class="coin-name">{{ $coin->name }}</div>
                        <div class="coin-ticker">{{ $coin->ticker }}</div>
                    </div>
                    <span style="color: var(--text-muted); font-size: 0.85rem;" title="View details">ⓘ</span>
                </div>

                <div class="coin-card-price">
                    <span class="coin-price">{{ $coin->formatted_price }}</span>
                    <span class="badge-change {{ $coin->change_24h >= 0 ? 'up' : 'down' }}">
                        {{ $coin->change_24h >= 0 ? '+' : '' }}{{ number_format($coin->change_24h, 2) }}%
                    </span>
                </div>

                <div class="coin-card-stats">
                    <div class="stat-col">
                        <span class="stat-label">24H</span>
                        <span class="stat-value">{{ $coin->formatted_volume }}</span>
                    </div>
                    <div class="stat-col">
                        <span class="stat-label">MCAP</span>
                        <span class="stat-value">{{ $coin->formatted_market_cap }}</span>
                    </div>
                    <div class="stat-col">
                        <span class="stat-label">HOLDERS</span>
                        <span class="stat-value">{{ $coin->formatted_holders }}</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</section>

<!-- 🆕 New Launches Section matching home.png -->
<section class="section" style="padding-top: 10px;">
    <div class="section-header">
        <h2 class="section-title">
            <span>🆕</span>
            <span>New Launches</span>
        </h2>
        <a href="#all-coins" class="btn btn-secondary btn-sm" style="font-size: 0.8rem;">View All</a>
    </div>

    <div class="coins-grid">
        @foreach($newLaunches as $coin)
            <a href="{{ route('coins.show', $coin->ticker) }}" class="coin-card">
                <div class="coin-card-header">
                    <img src="{{ asset('images/coins/' . $coin->logo_path) }}" alt="{{ $coin->name }}" class="coin-avatar" onerror="this.src='{{ asset('images/coins/pepeking.svg') }}'">
                    <div class="coin-meta">
                        <div class="coin-name">{{ $coin->name }}</div>
                        <div class="coin-ticker">{{ $coin->ticker }}</div>
                    </div>
                    <span style="color: var(--text-muted); font-size: 0.85rem;">⏱</span>
                </div>

                <div class="coin-card-price">
                    <span class="coin-price">{{ $coin->formatted_price }}</span>
                    <span class="badge-change {{ $coin->change_24h >= 0 ? 'up' : 'down' }}">
                        {{ $coin->change_24h >= 0 ? '+' : '' }}{{ number_format($coin->change_24h, 2) }}%
                    </span>
                </div>

                <div class="coin-card-stats">
                    <div class="stat-col">
                        <span class="stat-label">24H</span>
                        <span class="stat-value">{{ $coin->formatted_volume }}</span>
                    </div>
                    <div class="stat-col">
                        <span class="stat-label">MCAP</span>
                        <span class="stat-value">{{ $coin->formatted_market_cap }}</span>
                    </div>
                    <div class="stat-col">
                        <span class="stat-label">HOLDERS</span>
                        <span class="stat-value">{{ $coin->formatted_holders }}</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</section>

<!-- All Coins / Explore Table -->
<section id="all-coins" class="section">
    <div class="section-header">
        <h2 class="section-title">
            <span>⚡</span>
            <span>All Coins</span>
        </h2>
        <div style="color: var(--text-muted); font-size: 0.9rem;">
            Total Liquidity: <strong style="color: var(--text-primary); font-family: var(--font-mono);">${{ number_format($totalMarketCap, 0) }}</strong>
        </div>
    </div>

    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow-x: auto;">
        <table class="trades-table" style="min-width: 700px;">
            <thead>
                <tr>
                    <th>Coin</th>
                    <th>Price</th>
                    <th>24h Change</th>
                    <th>Market Cap</th>
                    <th>24h Volume</th>
                    <th>Holders</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allCoins as $coin)
                    <tr>
                        <td>
                            <a href="{{ route('coins.show', $coin->ticker) }}" style="display: flex; align-items: center; gap: 12px;">
                                <img src="{{ asset('images/coins/' . $coin->logo_path) }}" alt="{{ $coin->name }}" style="width: 32px; height: 32px; border-radius: 50%;" onerror="this.src='{{ asset('images/coins/pepeking.svg') }}'">
                                <div>
                                    <div style="font-weight: 700;">{{ $coin->name }}</div>
                                    <div style="color: var(--text-muted); font-size: 0.75rem;">{{ $coin->ticker }}</div>
                                </div>
                            </a>
                        </td>
                        <td style="font-family: var(--font-mono); font-weight: 700;">{{ $coin->formatted_price }}</td>
                        <td>
                            <span class="badge-change {{ $coin->change_24h >= 0 ? 'up' : 'down' }}">
                                {{ $coin->change_24h >= 0 ? '+' : '' }}{{ number_format($coin->change_24h, 2) }}%
                            </span>
                        </td>
                        <td style="font-family: var(--font-mono);">{{ $coin->formatted_market_cap }}</td>
                        <td style="font-family: var(--font-mono);">{{ $coin->formatted_volume }}</td>
                        <td style="font-family: var(--font-mono);">{{ $coin->formatted_holders }}</td>
                        <td style="text-align: right;">
                            <a href="{{ route('coins.show', $coin->ticker) }}" class="btn btn-primary btn-sm">Trade</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<!-- How It Works Section -->
<section id="how-it-works" class="section" style="padding-top: 10px; margin-bottom: 40px;">
    <div class="section-header">
        <h2 class="section-title">
            <span>💡</span>
            <span>How Pump Endless Works</span>
        </h2>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px;">
            <div style="font-size: 2.2rem; margin-bottom: 12px;">🚀</div>
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 8px;">1. Launch Instant Coins</h3>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                Create any meme coin in 10 seconds. Set the name, ticker, and upload a badge. It launches instantly on our high-speed simulated bonding curve.
            </p>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px;">
            <div style="font-size: 2.2rem; margin-bottom: 12px;">📈</div>
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 8px;">2. Buy, Sell & Moon</h3>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                Trade simulated SOL for tokens. As buying pressure mounts, the price automatically moves up and market cap surges with realistic candlestick charts.
            </p>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px;">
            <div style="font-size: 2.2rem; margin-bottom: 12px;">₿</div>
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 8px;">3. Swap to Bitcoin & Withdraw</h3>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                Lock in your meme coin profits by swapping directly to Bitcoin inside your demo wallet, then submit withdrawal requests.
            </p>
        </div>
    </div>
</section>

@endsection
