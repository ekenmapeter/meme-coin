@extends('layouts.admin')

@section('title', 'Admin Dashboard Overview – Pump Endless')
@section('header_title', 'Admin Dashboard')

@section('content')

<!-- 4 KPI Cards matching admin dashboard.png -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="kpi-title">Total Users</span>
            <span style="font-size: 1.2rem;">👥</span>
        </div>
        <div class="kpi-value">{{ number_format($totalUsers) }}</div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">
            registered accounts
        </div>
    </div>

    <div class="kpi-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="kpi-title">Total Coins</span>
            <span style="font-size: 1.2rem;">🪙</span>
        </div>
        <div class="kpi-value">{{ $totalCoins }}</div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">
            listed coins
        </div>
    </div>

    <div class="kpi-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="kpi-title">Total Volume</span>
            <span style="font-size: 1.2rem;">📊</span>
        </div>
        <div class="kpi-value">${{ number_format($totalVolume / 1000000, 2) }}M</div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">
            24h volume (all coins)
        </div>
    </div>

    <div class="kpi-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="kpi-title">Total Fees</span>
            <span style="font-size: 1.2rem;">💰</span>
        </div>
        <div class="kpi-value">${{ number_format($totalFees, 0) }}</div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">
            fees from swaps & withdrawals
        </div>
    </div>
</div>

<!-- Middle Row: Recent Coins & Top Traders matching admin dashboard.png -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
    
    <!-- Recent Coins Table -->
    <div class="widget-card" style="margin-bottom: 0;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 700;">Recent Coins</h3>
            <a href="{{ route('admin.coins.index') }}" class="btn btn-secondary btn-sm" style="font-size: 0.78rem;">View All</a>
        </div>

        <div style="overflow-x: auto;">
            <table class="trades-table">
                <thead>
                    <tr>
                        <th>Coin</th>
                        <th>Price</th>
                        <th>Market Cap</th>
                        <th>Holders</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentCoins as $coin)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <img src="{{ asset('images/coins/' . $coin->logo_path) }}" alt="{{ $coin->name }}" style="width: 28px; height: 28px; border-radius: 50%;" onerror="this.src='{{ asset('images/coins/pepeking.svg') }}'">
                                    <div>
                                        <strong style="font-size: 0.9rem;">{{ $coin->name }}</strong>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $coin->ticker }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-family: var(--font-mono); font-weight: 600;">{{ $coin->formatted_price }}</td>
                            <td style="font-family: var(--font-mono);">{{ $coin->formatted_market_cap }}</td>
                            <td style="font-family: var(--font-mono);">{{ $coin->formatted_holders }}</td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('admin.coins.edit', $coin->id) }}" class="btn btn-secondary btn-sm" style="padding: 4px 8px;" title="Edit & Control">✏️</a>
                                    <form action="{{ route('admin.coins.toggleFeature', $coin->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm" style="padding: 4px 8px; {{ $coin->is_featured ? 'color: #f59e0b;' : '' }}" title="Toggle Pin/Feature">★</button>
                                    </form>
                                    <form action="{{ route('admin.coins.togglePause', $coin->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm" style="padding: 4px 8px; {{ !$coin->is_active ? 'color: var(--accent-red);' : '' }}" title="Pause/Resume">
                                            {{ $coin->is_active ? '⏸' : '▶' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Traders -->
    <div class="widget-card" style="margin-bottom: 0;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 700;">Top Traders</h3>
            <span style="font-size: 0.8rem; color: var(--text-muted);">24H Volume</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            @foreach($topTraders as $idx => $trader)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; background: var(--bg-secondary); border-radius: var(--radius-sm);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-weight: 800; color: var(--text-muted); font-size: 0.8rem;">#{{ $idx + 1 }}</span>
                        <div style="font-family: var(--font-mono); font-size: 0.88rem; font-weight: 600;">
                            👾 {{ $trader['wallet'] }}
                        </div>
                    </div>
                    <div style="font-family: var(--font-mono); font-weight: 700; color: var(--accent-green);">
                        {{ $trader['volume'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>

<!-- Bottom Row: System Overview Chart & Recent Deposits matching admin dashboard.png -->
<div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 24px;">
    
    <!-- System Overview Chart -->
    <div class="widget-card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 700;">System Overview</h3>
            <span style="font-size: 0.8rem; color: var(--accent-green); background: rgba(0,240,118,0.1); padding: 2px 8px; border-radius: var(--radius-full);">This Week</span>
        </div>
        <div style="height: 240px; position: relative;">
            <canvas id="systemOverviewCanvas"></canvas>
        </div>
    </div>

    <!-- Recent Deposits with One-Click Confirmation -->
    <div class="widget-card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 700;">Recent Deposits</h3>
            <a href="{{ route('admin.deposits.index') }}" class="btn btn-secondary btn-sm" style="font-size: 0.78rem;">View All ({{ $pendingDeposits }} pending)</a>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            @forelse($recentDeposits as $dep)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--bg-secondary); border-radius: var(--radius-sm);">
                    <div>
                        <div style="display: flex; align-items: center; gap: 6px; font-family: var(--font-mono); font-size: 0.85rem; font-weight: 600;">
                            <span>👾</span>
                            <span>{{ $dep->user ? substr($dep->user->wallet_address ?? '0x8f...a1b2', 0, 8) . '...' : 'Unknown' }}</span>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                            TXID: {{ $dep->short_txid }}
                        </div>
                    </div>

                    <div style="text-align: right;">
                        <div style="font-family: var(--font-mono); font-weight: 700;">
                            {{ number_format($dep->amount, 4) }} {{ $dep->currency }}
                        </div>
                        <div style="margin-top: 4px;">
                            @if($dep->status === 'pending')
                                <form action="{{ route('admin.deposits.status', $dep->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="btn btn-primary btn-sm" style="padding: 2px 8px; font-size: 0.7rem;">Confirm</button>
                                </form>
                                <form action="{{ route('admin.deposits.status', $dep->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn btn-danger btn-sm" style="padding: 2px 8px; font-size: 0.7rem;">Reject</button>
                                </form>
                            @else
                                <span class="badge {{ $dep->status === 'confirmed' ? 'badge-success' : 'badge-danger' }}" style="font-size: 0.7rem;">
                                    {{ ucfirst($dep->status) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div style="color: var(--text-muted); font-size: 0.85rem; text-align: center; padding: 20px;">No deposit requests found.</div>
            @endforelse
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('systemOverviewCanvas').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, 'rgba(0, 240, 118, 0.3)');
        gradient.addColorStop(1, 'rgba(0, 240, 118, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Platform Activity',
                    data: [120, 190, 160, 280, 240, 360, 420],
                    borderColor: '#00f076',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#00f076',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#64748b' } },
                    y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#64748b' } }
                }
            }
        });
    });
</script>
@endpush
@endsection
