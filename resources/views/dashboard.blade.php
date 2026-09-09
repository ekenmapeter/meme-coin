@extends('layouts.app')

@section('title', 'Dashboard – ' . \App\Models\PlatformSetting::get('site_name', 'Pump Endless'))

@section('content')

<div class="section">
    <!-- Welcome -->
    <div class="section-header" style="flex-wrap: wrap; gap: 14px;">
        <div>
            <h2 style="font-size: 1.6rem; font-weight: 800; letter-spacing: -0.5px;">
                Welcome back, <span style="color: var(--accent-green);">{{ $user->name }}</span>
                <i class="fa-solid fa-hand-sparkles" style="color: var(--accent-yellow); font-size: 1.2rem;"></i>
            </h2>
            <div style="color: var(--text-muted); font-size: 0.85rem; font-family: var(--font-mono);">
                <i class="fa-solid fa-circle" style="color: var(--accent-green); font-size: 0.55rem; vertical-align: middle;"></i>
                {{ substr($user->wallet_address ?? 'no wallet', 0, 10) }}...{{ substr($user->wallet_address ?? '', -6) }}
            </div>
        </div>
        <a href="{{ route('coins.launch') }}" class="btn btn-primary">
            <i class="fa-solid fa-bolt"></i> Launch a Coin
        </a>
    </div>

    <!-- Portfolio overview -->
    <div class="kpi-grid dashboard-kpi-grid">
        <div class="kpi-card glow-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span class="kpi-title">Portfolio Value</span>
                <i class="fa-solid fa-sack-dollar" style="color: var(--accent-green);"></i>
            </div>
            <div class="kpi-value" style="font-size: 1.5rem;">${{ number_format($portfolioUsd, 2) }}</div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">Simulated total (BTC + SOL + USD + tokens)</div>
        </div>

        <div class="kpi-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span class="kpi-title">Bitcoin</span>
                <i class="fa-brands fa-bitcoin" style="color: #f7931a;"></i>
            </div>
            <div class="kpi-value" style="font-size: 1.3rem; color: #f7931a;">{{ sprintf('%.6f', $user->btc_balance) }}</div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">≈ ${{ number_format($user->btc_balance * $btcPriceUsd, 2) }}</div>
        </div>

        <div class="kpi-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span class="kpi-title">Solana</span>
                <i class="fa-solid fa-circle" style="color: #14f195; font-size: 0.8rem;"></i>
            </div>
            <div class="kpi-value" style="font-size: 1.3rem; color: #14f195;">{{ number_format($user->sol_balance, 4) }}</div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">≈ ${{ number_format($user->sol_balance * $solPriceUsd, 2) }}</div>
        </div>

        <div class="kpi-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span class="kpi-title">USD Cash</span>
                <i class="fa-solid fa-dollar-sign" style="color: var(--text-muted);"></i>
            </div>
            <div class="kpi-value" style="font-size: 1.3rem;">${{ number_format($user->usd_balance, 2) }}</div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">Available balance</div>
        </div>
    </div>

    <!-- Quick actions -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; margin: 22px 0;">
        <a href="{{ route('coins.index') }}" class="quick-action"><i class="fa-solid fa-arrow-trend-up"></i><span>Trade Coins</span></a>
        <a href="{{ route('wallet.index', ['tab' => 'swap']) }}" class="quick-action"><i class="fa-solid fa-arrow-right-arrow-left"></i><span>Swap to BTC</span></a>
        <a href="{{ route('wallet.index', ['tab' => 'deposit']) }}" class="quick-action"><i class="fa-solid fa-right-to-bracket"></i><span>Deposit</span></a>
        <a href="{{ route('wallet.index', ['tab' => 'withdraw']) }}" class="quick-action"><i class="fa-solid fa-right-from-bracket"></i><span>Withdraw</span></a>
        <a href="{{ route('coins.launch') }}" class="quick-action"><i class="fa-solid fa-bolt"></i><span>Launch Coin</span></a>
    </div>

    <div class="dashboard-two-col">
        <!-- Holdings -->
        <div class="widget-card" style="margin-bottom: 0;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                <h3 style="font-size: 1.15rem; font-weight: 800;"><i class="fa-solid fa-coins" style="color: var(--accent-green);"></i> Your Holdings</h3>
                <a href="{{ route('wallet.index') }}" style="font-size: 0.8rem; color: var(--accent-green); font-weight: 700;">Manage Wallet <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            @if($holdings->isEmpty())
                <div style="text-align: center; padding: 34px 20px; color: var(--text-muted);">
                    <div style="font-size: 2.2rem; margin-bottom: 10px;"><i class="fa-solid fa-box-open"></i></div>
                    <div style="font-weight: 700; color: var(--text-secondary);">No holdings yet</div>
                    <div style="font-size: 0.85rem; margin-top: 4px;">Start trading on the <a href="{{ route('coins.index') }}" style="color: var(--accent-green);">marketplace</a> to build your portfolio.</div>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table class="trades-table">
                    <thead>
                        <tr>
                            <th>Coin</th>
                            <th style="text-align: right;">Balance</th>
                            <th style="text-align: right;">Value</th>
                            <th style="text-align: right;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($holdings as $h)
                            <tr>
                                <td>
                                    <a href="{{ route('coins.show', $h['coin']->ticker) }}" style="display: flex; align-items: center; gap: 10px;">
                                        <img src="{{ $h['coin']->logo_url }}" alt="{{ $h['coin']->name }}" style="width: 28px; height: 28px; border-radius: 50%;" onerror="this.src='{{ asset('images/coins/pepeking.svg') }}'">
                                        <div>
                                            <div style="font-weight: 700;">{{ $h['coin']->name }}</div>
                                            <div style="color: var(--text-muted); font-size: 0.72rem;">{{ $h['coin']->ticker }}</div>
                                        </div>
                                    </a>
                                </td>
                                <td style="text-align: right; font-family: var(--font-mono); font-weight: 700;">
                                    {{ number_format($h['token_balance']) }}
                                </td>
                                <td style="text-align: right; font-family: var(--font-mono); color: var(--accent-green);">
                                    ${{ number_format($h['current_value_usd'], 2) }}
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ route('coins.show', $h['coin']->ticker) }}" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 0.72rem;">Trade</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Recent activity -->
        <div class="widget-card" style="margin-bottom: 0;">
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 14px;"><i class="fa-solid fa-clock-rotate-left" style="color: var(--accent-blue);"></i> Recent Activity</h3>

            @php
                $activity = collect();
                foreach ($recentSwaps as $s) {
                    $activity->push(['icon' => 'fa-arrow-right-arrow-left', 'color' => 'var(--accent-blue)', 'text' => 'Swapped ' . number_format($s->token_amount) . ' ' . ($s->coin->ticker ?? '') . ' → ' . sprintf('%.6f', $s->net_btc_received) . ' BTC', 'time' => $s->created_at]);
                }
                foreach ($recentDeposits as $d) {
                    $activity->push(['icon' => 'fa-right-to-bracket', 'color' => 'var(--accent-green)', 'text' => 'Deposited ' . number_format($d->amount, 4) . ' ' . $d->currency . ' (' . ucfirst($d->status) . ')', 'time' => $d->created_at]);
                }
                foreach ($recentWithdrawals as $w) {
                    $activity->push(['icon' => 'fa-right-from-bracket', 'color' => 'var(--accent-red)', 'text' => 'Withdrew ' . number_format($w->amount, 4) . ' ' . $w->currency . ' (' . ucfirst($w->status) . ')', 'time' => $w->created_at]);
                }
                $activity = $activity->sortByDesc('time')->take(8);
            @endphp

            @if($activity->isEmpty())
                <div style="text-align: center; padding: 34px 20px; color: var(--text-muted);">
                    <div style="font-size: 2.2rem; margin-bottom: 10px;"><i class="fa-solid fa-receipt"></i></div>
                    <div style="font-weight: 700; color: var(--text-secondary);">No activity yet</div>
                    <div style="font-size: 0.85rem; margin-top: 4px;">Deposits, swaps and withdrawals will appear here.</div>
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($activity as $a)
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <span style="width: 34px; height: 34px; flex-shrink: 0; border-radius: 10px; background: rgba(255,255,255,0.04); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; color: {{ $a['color'] }};">
                                <i class="fa-solid {{ $a['icon'] }}"></i>
                            </span>
                            <div style="min-width: 0;">
                                <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-primary);">{{ $a['text'] }}</div>
                                <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $a['time']->diffForHumans() }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Top coins -->
    <div class="widget-card" style="margin-top: 22px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <h3 style="font-size: 1.15rem; font-weight: 800;"><i class="fa-solid fa-fire" style="color: var(--accent-green);"></i> Trending Markets</h3>
            <a href="{{ route('coins.index') }}" style="font-size: 0.8rem; color: var(--accent-green); font-weight: 700;">View All <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
            @foreach($topCoins as $coin)
                <a href="{{ route('coins.show', $coin->ticker) }}" class="coin-card" style="padding: 14px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <img src="{{ $coin->logo_url }}" alt="{{ $coin->name }}" style="width: 32px; height: 32px; border-radius: 50%;" onerror="this.src='{{ asset('images/coins/pepeking.svg') }}'">
                        <div style="min-width: 0; flex: 1;">
                            <div style="font-weight: 700; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $coin->name }}</div>
                            <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $coin->ticker }}</div>
                        </div>
                        <span class="badge-change {{ $coin->change_24h >= 0 ? 'up' : 'down' }}">{{ $coin->change_24h >= 0 ? '+' : '' }}{{ number_format($coin->change_24h, 2) }}%</span>
                    </div>
                    <div style="margin-top: 10px; font-family: var(--font-mono); font-weight: 700; font-size: 0.95rem;">{{ $coin->formatted_price }}</div>
                </a>
            @endforeach
        </div>
    </div>
</div>

@endsection