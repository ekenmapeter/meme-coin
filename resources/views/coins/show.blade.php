@extends('layouts.app')

@section('title', "{$coin->name} ({$coin->ticker}) Price, Chart & Trade – Pump Endless")

@section('content')
<div class="section" style="padding-top: 15px;">
    <!-- Breadcrumbs matching coin-page.png -->
    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 16px;">
        <a href="{{ route('home') }}" style="color: var(--text-secondary);">Home</a> &gt; 
        <span>Coins</span> &gt; 
        <span style="color: var(--text-primary); font-weight: 600;">{{ $coin->name }}</span>
    </div>

    <!-- Coin Header matching coin-page.png -->
    <div class="coin-page-header">
        <div class="coin-page-title">
            <img src="{{ $coin->logo_url }}" alt="{{ $coin->name }}" class="coin-page-avatar" onerror="this.src='{{ asset('images/coins/pepeking.svg') }}'">
            <div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h1 style="font-size: 2rem; font-weight: 900; letter-spacing: -0.5px;">{{ $coin->name }}</h1>
                    <span style="color: var(--text-muted); font-size: 1.1rem; font-weight: 600;">{{ $coin->ticker }}</span>
                    <button type="button" onclick="copyContract('{{ $coin->contract_address }}')" style="background:none; border:none; color:var(--text-muted); cursor:pointer;" title="Copy Contract">⎘</button>
                </div>
                <div style="color: var(--text-secondary); font-size: 0.85rem;">
                    Network: <strong style="color: var(--accent-green);">{{ $coin->network }}</strong> • Total Supply: {{ number_format($coin->total_supply) }}
                </div>
            </div>
        </div>

        <div style="text-align: right;">
            <div id="liveCoinPrice" style="font-size: 2.2rem; font-weight: 900; font-family: var(--font-mono); color: var(--accent-green); text-shadow: 0 0 15px var(--accent-green-glow);">
                {{ $coin->formatted_price }}
            </div>
            <div id="liveCoinChange" class="badge-change {{ $coin->change_24h >= 0 ? 'up' : 'down' }}" style="display: inline-block; font-size: 1rem; padding: 4px 12px;">
                {{ $coin->change_24h >= 0 ? '+' : '' }}{{ number_format($coin->change_24h, 2) }}%
            </div>
        </div>
    </div>

    <!-- Stats Bar matching coin-page.png -->
    <div class="coin-stats-bar">
        <div>
            <div class="stat-label">Market Cap</div>
            <div id="liveMarketCap" style="font-size: 1.25rem; font-weight: 700; font-family: var(--font-mono); margin-top: 4px;">
                ${{ number_format($coin->market_cap, 0) }}
            </div>
        </div>
        <div>
            <div class="stat-label">Holders</div>
            <div id="liveHolders" style="font-size: 1.25rem; font-weight: 700; font-family: var(--font-mono); margin-top: 4px;">
                {{ number_format($coin->holders_count) }}
            </div>
        </div>
        <div>
            <div class="stat-label">Buyers</div>
            <div id="liveBuyers" style="font-size: 1.25rem; font-weight: 700; font-family: var(--font-mono); margin-top: 4px;">
                {{ number_format($coin->buyers_count) }}
            </div>
        </div>
        <div>
            <div class="stat-label">Volume (24h)</div>
            <div id="liveVolume" style="font-size: 1.25rem; font-weight: 700; font-family: var(--font-mono); margin-top: 4px;">
                ${{ number_format($coin->volume_24h, 0) }}
            </div>
        </div>
        <div>
            <div class="stat-label">Liquidity</div>
            <div style="font-size: 1.25rem; font-weight: 700; font-family: var(--font-mono); margin-top: 4px; color: var(--accent-green);">
                ${{ number_format($coin->liquidity, 0) }}
            </div>
        </div>
    </div>

    <!-- Main Layout: Chart & Trades (Left) / Buy-Sell Widget & Info (Right) -->
    <div class="coin-detail-layout">
        <!-- Left Side -->
        <div>
            <!-- Chart Container -->
            <div class="widget-card" style="padding: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                    <div style="display: flex; gap: 16px; font-weight: 700; font-size: 0.95rem;">
                        <span style="color: var(--accent-green); border-bottom: 2px solid var(--accent-green); padding-bottom: 4px; cursor: pointer;">Chart</span>
                        <span style="color: var(--text-muted); cursor: pointer;" onclick="scrollToTrades()">Trade History</span>
                        <span style="color: var(--text-muted); cursor: pointer;">Holders</span>
                    </div>

                    <!-- Timeframe Selectors matching coin-page.png -->
                    <div style="display: flex; gap: 6px; background: var(--bg-secondary); padding: 4px; border-radius: var(--radius-sm);">
                        @foreach(['1m', '5m', '15m', '1h', '4h', '1D'] as $tf)
                            <button type="button" class="btn btn-secondary btn-sm tf-btn {{ $tf === '1D' ? 'active' : '' }}" onclick="changeTimeframe('{{ $tf }}', this)" style="padding: 4px 10px; font-size: 0.8rem; border:none; {{ $tf === '1D' ? 'background: var(--bg-card); color: var(--accent-green); font-weight:700;' : '' }}">
                                {{ $tf }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Ticker Header on Chart -->
                <div style="font-family: var(--font-mono); font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 10px; display: flex; align-items: center; gap: 12px;">
                    <strong style="color: var(--text-primary);">{{ $coin->ticker }}/USD</strong>
                    <span id="chartCandleInfo">O: {{ sprintf('%.6f', $coin->current_price * 0.98) }} H: {{ sprintf('%.6f', $coin->current_price * 1.04) }} L: {{ sprintf('%.6f', $coin->current_price * 0.96) }} C: {{ sprintf('%.6f', $coin->current_price) }}</span>
                </div>

                <!-- Interactive Chart Canvas -->
                <div style="position: relative; height: 380px; width: 100%;">
                    <canvas id="priceChartCanvas"></canvas>
                </div>
            </div>

            <!-- Recent Trades Section matching coin-page.png -->
            <div id="tradesSection" class="widget-card">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <h3 style="font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <span>Recent Trades</span>
                        <span style="font-size: 0.75rem; color: var(--accent-green); background: rgba(0,240,118,0.1); padding: 2px 8px; border-radius: var(--radius-full); font-weight: normal;">Live Simulation ●</span>
                    </h3>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Auto-updating</span>
                </div>

                <div style="overflow-x: auto;">
                    <table class="trades-table">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th>Action</th>
                                <th>Value</th>
                                <th>Amount</th>
                                <th style="text-align: right;">Time</th>
                            </tr>
                        </thead>
                        <tbody id="recentTradesTbody">
                            @foreach($recentTrades as $trade)
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px; font-family: var(--font-mono);">
                                            <span><i class="fa-solid fa-gamepad"></i></span>
                                            <span>{{ $trade->short_wallet }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="{{ $trade->type === 'buy' ? 'trade-buy' : 'trade-sell' }}">
                                            {{ $trade->type === 'buy' ? 'Bought' : 'Sold' }}
                                        </span>
                                    </td>
                                    <td style="font-family: var(--font-mono); font-weight: 600;">
                                        ${{ number_format($trade->usd_amount, 2) }}
                                    </td>
                                    <td style="font-family: var(--font-mono);">
                                        {{ $trade->formatted_token_amount }} {{ $coin->ticker }}
                                    </td>
                                    <td style="text-align: right; color: var(--text-muted); font-size: 0.8rem;">
                                        {{ $trade->created_at->diffForHumans(null, true, true) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Side: Buy / Sell Widget & About Coin -->
        <div>
            <!-- Trading Widget matching coin-page.png -->
            <div class="widget-card">
                <div class="widget-tabs">
                    <button type="button" class="widget-tab active" id="tabBuy" onclick="switchTradeTab('buy')">Buy</button>
                    <button type="button" class="widget-tab" id="tabSell" onclick="switchTradeTab('sell')">Sell</button>
                </div>

                <form id="tradeForm" onsubmit="handleTradeSubmit(event)">
                    <input type="hidden" id="tradeType" value="buy">

                    <!-- You Pay Input -->
                    <div class="input-group">
                        <div class="input-label">
                            <span id="labelYouPay">You Pay</span>
                            <span style="font-size: 0.8rem;">
                                Balance: <strong id="tradePayBalance" style="color: var(--text-primary); font-family: var(--font-mono);">
                                    {{ Auth::check() ? number_format(Auth::user()->sol_balance, 4) . ' SOL' : '0.00 SOL' }}
                                </strong>
                            </span>
                        </div>
                        <div class="input-field-wrap">
                            <input type="number" step="any" min="0.0001" id="tradeAmount" placeholder="0.0" oninput="calculateTradePreview()" required>
                            <div style="display: flex; align-items: center; gap: 6px; background: var(--bg-secondary); padding: 4px 10px; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 700;" id="payCurrencyBadge">
                                <span>◎</span>
                                <span>SOL</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Percent Buttons -->
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; margin-bottom: 16px;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="setTradePercent(0.25)" style="padding: 4px; font-size: 0.75rem;">25%</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="setTradePercent(0.50)" style="padding: 4px; font-size: 0.75rem;">50%</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="setTradePercent(0.75)" style="padding: 4px; font-size: 0.75rem;">75%</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="setTradePercent(1.00)" style="padding: 4px; font-size: 0.75rem;">100%</button>
                    </div>

                    <!-- You Receive Preview -->
                    <div class="input-group">
                        <div class="input-label">
                            <span id="labelYouReceive">You Receive</span>
                            <span style="font-size: 0.8rem;">
                                Holding: <strong id="userHoldingDisplay" style="color: var(--text-primary); font-family: var(--font-mono);">
                                    {{ $userHolding ? number_format($userHolding->token_balance) : '0' }} {{ $coin->ticker }}
                                </strong>
                            </span>
                        </div>
                        <div class="input-field-wrap" style="background: rgba(255,255,255,0.02);">
                            <input type="text" id="tradeReceivePreview" placeholder="0.0" readonly style="color: var(--accent-green);">
                            <div style="display: flex; align-items: center; gap: 6px; background: var(--bg-secondary); padding: 4px 10px; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 700;" id="receiveCurrencyBadge">
                                <img src="{{ $coin->logo_url }}" style="width: 16px; height: 16px; border-radius: 50%;" onerror="this.src='{{ asset('images/coins/pepeking.svg') }}'">
                                <span>{{ $coin->ticker }}</span>
                            </div>
                        </div>
                    </div>

                    @if(Auth::check())
                        <button type="submit" id="btnExecuteTrade" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1rem; border-radius: var(--radius-md);">
                            Place Buy Order
                        </button>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1rem; border-radius: var(--radius-md); display: block; text-align: center;">
                            Login to Trade
                        </a>
                    @endif
                </form>
            </div>

            <!-- About Coin matching coin-page.png -->
            <div class="widget-card">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 12px;">About {{ $coin->name }}</h3>
                <p style="color: var(--text-secondary); font-size: 0.9rem; line-height: 1.6; margin-bottom: 20px;">
                    {{ $coin->description ?: 'Community-driven decentralized meme coin on Solana with automated simulated bonding curve and zero dev transaction tax.' }}
                </p>

                <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.88rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 8px;">
                        <span style="color: var(--text-muted);">Contract Address</span>
                        <div style="display: flex; align-items: center; gap: 6px; font-family: var(--font-mono);">
                            <span>{{ substr($coin->contract_address, 0, 6) }}...{{ substr($coin->contract_address, -6) }}</span>
                            <button type="button" onclick="copyContract('{{ $coin->contract_address }}')" style="background:none; border:none; color:var(--accent-green); cursor:pointer;" title="Copy">⎘</button>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 8px;">
                        <span style="color: var(--text-muted);">Network</span>
                        <span style="font-weight: 600; color: var(--accent-green);">{{ $coin->network }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 8px;">
                        <span style="color: var(--text-muted);">Launched</span>
                        <span style="font-weight: 600;">{{ $coin->created_at->format('M d, Y') }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted);">Swap To BTC</span>
                        <a href="{{ route('wallet.index', ['tab' => 'swap']) }}" style="color: var(--accent-green); font-weight: 700;">Swap in Wallet →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const coinTicker = "{{ $coin->ticker }}";
    let coinPrice = {{ $coin->current_price }};
    const solUsdPrice = {{ $solPrice }};
    let userSolBalance = {{ Auth::check() ? Auth::user()->sol_balance : 0 }};
    let userTokenBalance = {{ $userHolding ? $userHolding->token_balance : 0 }};
    let activeTradeType = 'buy';
    let chartInstance = null;

    function copyContract(text) {
        navigator.clipboard.writeText(text);
        alert('Contract address copied: ' + text);
    }

    function scrollToTrades() {
        document.getElementById('tradesSection').scrollIntoView({ behavior: 'smooth' });
    }

    // Trade Mode Switch
    function switchTradeTab(type) {
        activeTradeType = type;
        document.getElementById('tradeType').value = type;
        const tabBuy = document.getElementById('tabBuy');
        const tabSell = document.getElementById('tabSell');
        const btn = document.getElementById('btnExecuteTrade');
        const labelPay = document.getElementById('labelYouPay');
        const labelReceive = document.getElementById('labelYouReceive');
        const payBadge = document.getElementById('payCurrencyBadge');
        const receiveBadge = document.getElementById('receiveCurrencyBadge');
        const payBalance = document.getElementById('tradePayBalance');

        if (type === 'buy') {
            tabBuy.classList.add('active');
            tabSell.classList.remove('active');
            if (btn) {
                btn.className = 'btn btn-primary';
                btn.innerText = 'Place Buy Order';
            }
            labelPay.innerText = 'You Pay';
            labelReceive.innerText = 'You Receive';
            payBadge.innerHTML = '<span>◎</span><span>SOL</span>';
            receiveBadge.innerHTML = `<img src="{{ $coin->logo_url }}" style="width:16px;height:16px;border-radius:50%;" onerror="this.src='/images/coins/pepeking.svg'"><span>{{ $coin->ticker }}</span>`;
            payBalance.innerText = userSolBalance.toFixed(4) + ' SOL';
        } else {
            tabSell.classList.add('active');
            tabBuy.classList.remove('active');
            if (btn) {
                btn.className = 'btn btn-danger';
                btn.innerText = 'Place Sell Order';
            }
            labelPay.innerText = 'You Sell';
            labelReceive.innerText = 'You Receive';
            payBadge.innerHTML = `<img src="{{ $coin->logo_url }}" style="width:16px;height:16px;border-radius:50%;" onerror="this.src='/images/coins/pepeking.svg'"><span>{{ $coin->ticker }}</span>`;
            receiveBadge.innerHTML = '<span>◎</span><span>SOL</span>';
            payBalance.innerText = Number(userTokenBalance).toLocaleString() + ' {{ $coin->ticker }}';
        }

        document.getElementById('tradeAmount').value = '';
        document.getElementById('tradeReceivePreview').value = '';
    }

    function setTradePercent(pct) {
        const input = document.getElementById('tradeAmount');
        if (activeTradeType === 'buy') {
            input.value = (userSolBalance * pct).toFixed(4);
        } else {
            input.value = Math.floor(userTokenBalance * pct);
        }
        calculateTradePreview();
    }

    function calculateTradePreview() {
        const amount = parseFloat(document.getElementById('tradeAmount').value) || 0;
        const preview = document.getElementById('tradeReceivePreview');

        if (activeTradeType === 'buy') {
            const usd = amount * solUsdPrice;
            const tokens = coinPrice > 0 ? (usd / coinPrice) : 0;
            preview.value = tokens > 0 ? Number(tokens.toFixed(0)).toLocaleString() : '0';
        } else {
            const usd = amount * coinPrice;
            const sol = solUsdPrice > 0 ? (usd / solUsdPrice) : 0;
            preview.value = sol > 0 ? sol.toFixed(4) + ' SOL' : '0.0000 SOL';
        }
    }

    async function handleTradeSubmit(e) {
        e.preventDefault();
        @if(!Auth::check())
            window.location.href = '{{ route('login') }}';
            return;
        @endif

        const amount = document.getElementById('tradeAmount').value;
        const type = activeTradeType;
        const btn = document.getElementById('btnExecuteTrade');

        btn.disabled = true;
        btn.innerText = 'Processing Order...';

        try {
            const res = await fetch(`{{ route('coins.trade', $coin->ticker) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    type: type,
                    amount: amount,
                    currency: 'SOL'
                })
            });

            const data = await res.json();
            if (data.error) {
                alert('Trade Failed: ' + data.error);
            } else {
                userSolBalance = data.user_sol_balance;
                userTokenBalance = data.user_token_balance;
                document.getElementById('userHoldingDisplay').innerText = Number(userTokenBalance).toLocaleString() + ' {{ $coin->ticker }}';
                switchTradeTab(activeTradeType);
                pollCoinData(); // refresh live trades & price immediately
                alert(`Success! ${type.toUpperCase()} executed at \$${Number(data.price).toFixed(6)}.`);
            }
        } catch (err) {
            console.error(err);
            alert('An unexpected error occurred during trade execution.');
        } finally {
            btn.disabled = false;
            switchTradeTab(activeTradeType);
        }
    }

    // Chart.js initialization with realistic candlestick/line
    async function loadChart(timeframe = '1D') {
        try {
            const res = await fetch(`/api/coins/${coinTicker}/chart?timeframe=${timeframe}`);
            const data = await res.json();
            const candles = data.candles;

            const labels = candles.map(c => {
                const d = new Date(c.time * 1000);
                return timeframe === '1D' ? `${d.getMonth()+1}/${d.getDate()}` : `${d.getHours()}:${d.getMinutes() < 10 ? '0' : ''}${d.getMinutes()}`;
            });

            const prices = candles.map(c => c.close);
            const volumes = candles.map(c => c.volume);

            const ctx = document.getElementById('priceChartCanvas').getContext('2d');

            const gradient = ctx.createLinearGradient(0, 0, 0, 360);
            gradient.addColorStop(0, 'rgba(0, 240, 118, 0.25)');
            gradient.addColorStop(1, 'rgba(0, 240, 118, 0.0)');

            if (chartInstance) {
                chartInstance.destroy();
            }

            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Price (USD)',
                            data: prices,
                            borderColor: '#00f076',
                            borderWidth: 2,
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.2,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: '#00f076',
                            yAxisID: 'yPrice',
                        },
                        {
                            type: 'bar',
                            label: 'Volume',
                            data: volumes,
                            backgroundColor: 'rgba(255, 255, 255, 0.08)',
                            yAxisID: 'yVol',
                            barThickness: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#121826',
                            titleColor: '#94a3b8',
                            bodyColor: '#00f076',
                            borderColor: '#1e293b',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    if (context.dataset.label === 'Price (USD)') {
                                        return 'Price: $' + Number(context.raw).toFixed(6);
                                    }
                                    return 'Vol: $' + Number(context.raw).toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(255, 255, 255, 0.04)' },
                            ticks: { color: '#64748b', maxTicksLimit: 8 }
                        },
                        yPrice: {
                            position: 'right',
                            grid: { color: 'rgba(255, 255, 255, 0.04)' },
                            ticks: {
                                color: '#00f076',
                                font: { family: 'monospace' },
                                callback: val => '$' + Number(val).toFixed(6)
                            }
                        },
                        yVol: {
                            position: 'left',
                            display: false,
                            max: Math.max(...volumes) * 3,
                        }
                    }
                }
            });
        } catch (err) {
            console.error('Error loading chart data:', err);
        }
    }

    function changeTimeframe(tf, btn) {
        document.querySelectorAll('.tf-btn').forEach(b => {
            b.style.background = 'transparent';
            b.style.color = 'var(--text-secondary)';
            b.style.fontWeight = 'normal';
        });
        btn.style.background = 'var(--bg-card)';
        btn.style.color = 'var(--accent-green)';
        btn.style.fontWeight = '700';
        loadChart(tf);
    }

    // Live Polling for recent trades & price
    async function pollCoinData() {
        try {
            const res = await fetch(`/api/coins/${coinTicker}/trades`);
            const data = await res.json();

            coinPrice = data.current_price;
            document.getElementById('liveCoinPrice').innerText = data.formatted_price;
            
            const changeEl = document.getElementById('liveCoinChange');
            changeEl.innerText = (data.change_24h >= 0 ? '+' : '') + Number(data.change_24h).toFixed(2) + '%';
            changeEl.className = 'badge-change ' + (data.change_24h >= 0 ? 'up' : 'down');

            document.getElementById('liveMarketCap').innerText = data.market_cap;
            document.getElementById('liveVolume').innerText = data.volume_24h;
            document.getElementById('liveHolders').innerText = data.holders;
            document.getElementById('liveBuyers').innerText = data.buyers;

            // Update trades table
            if (data.trades && data.trades.length > 0) {
                const tbody = document.getElementById('recentTradesTbody');
                tbody.innerHTML = data.trades.map(t => `
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px; font-family:var(--font-mono);">
                                <span><i class="fa-solid fa-gamepad"></i></span>
                                <span>${t.wallet}</span>
                            </div>
                        </td>
                        <td>
                            <span class="${t.type === 'buy' ? 'trade-buy' : 'trade-sell'}">
                                ${t.type === 'buy' ? 'Bought' : 'Sold'}
                            </span>
                        </td>
                        <td style="font-family:var(--font-mono); font-weight:600;">$${t.usd_amount}</td>
                        <td style="font-family:var(--font-mono);">${t.token_amount} {{ $coin->ticker }}</td>
                        <td style="text-align:right; color:var(--text-muted); font-size:0.8rem;">${t.time_ago}</td>
                    </tr>
                `).join('');
            }
        } catch (err) {
            console.error('Error polling coin data:', err);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadChart('1D');
        setInterval(pollCoinData, 4000);
    });
</script>
@endpush
@endsection
