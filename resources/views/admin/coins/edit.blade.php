@extends('layouts.admin')

@section('title', "Edit & Control {$coin->name} – Pump Endless Admin")
@section('header_title', "Control Center: {$coin->name} ({$coin->ticker})")

@section('content')

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">

    <!-- Left Box: Instant Price Control & Simulated Trade Generator -->
    <div>
        <!-- Quick Manual Price Override -->
        <div class="widget-card" style="border-color: rgba(0, 240, 118, 0.4); margin-bottom: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--accent-green);"><i class="fa-solid fa-bolt"></i> Live Price Override</h3>
                <span class="badge badge-success">{{ $coin->formatted_price }}</span>
            </div>
            <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 16px;">
                Directly force the market price of this coin. The chart candlestick and market cap will instantly update.
            </p>

            <form action="{{ route('admin.coins.quickPrice', $coin->id) }}" method="POST" style="display: flex; gap: 10px;">
                @csrf
                <div class="input-field-wrap" style="flex: 1;">
                    <span>$</span>
                    <input type="number" step="any" name="price" value="{{ sprintf('%.8f', $coin->current_price) }}" required>
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                    Set Price Now
                </button>
            </form>
        </div>

        <!-- Simulated Trade Injector -->
        <div class="widget-card" style="margin-bottom: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                <h3 style="font-size: 1.15rem; font-weight: 800;"><i class="fa-solid fa-dice"></i> Inject Simulated Trade</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Bot Tape</span>
            </div>
            <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 16px;">
                Inject a simulated Buy or Sell transaction. This records to the recent trades stream and dynamically nudges the bonding curve.
            </p>

            <form action="{{ route('admin.coins.simulateTrade', $coin->id) }}" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div class="input-group" style="margin-bottom: 0;">
                        <label class="input-label">Trade Action</label>
                        <select name="type" style="width: 100%; background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary); padding: 10px; border-radius: var(--radius-sm); font-weight: 700;">
                            <option value="buy"><span class="status-dot" style="background:#22c55e"></span> Buy (Pumps Price Up)</option>
                            <option value="sell"><span class="status-dot" style="background:#ef4444"></span> Sell (Pulls Price Down)</option>
                        </select>
                    </div>

                    <div class="input-group" style="margin-bottom: 0;">
                        <label class="input-label">Trade Volume (USD)</label>
                        <div class="input-field-wrap">
                            <span>$</span>
                            <input type="number" step="10" name="usd_amount" value="500" min="10" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-secondary" style="width: 100%; border-color: var(--accent-green); color: var(--accent-green); font-weight: 700;">
                    Execute Simulated Trade
                </button>
            </form>
        </div>

        <!-- Recent Simulated Trades for this coin -->
        <div class="widget-card">
            <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 12px;">Recent Coin Activity</h3>
            <table class="trades-table" style="font-size: 0.82rem;">
                <thead>
                    <tr>
                        <th>Wallet</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTrades as $trade)
                        <tr>
                            <td style="font-family: var(--font-mono);">{{ $trade->short_wallet }}</td>
                            <td>
                                <span class="{{ $trade->type === 'buy' ? 'trade-buy' : 'trade-sell' }}">
                                    {{ strtoupper($trade->type) }}
                                </span>
                            </td>
                            <td style="font-family: var(--font-mono);">${{ number_format($trade->usd_amount, 2) }}</td>
                            <td style="color: var(--text-muted);">{{ $trade->created_at->diffForHumans(null, true, true) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Box: Edit Details & Auto Simulation Engine -->
    <div class="widget-card">
        <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 16px;">Coin Configuration</h3>

        <form action="{{ route('admin.coins.update', $coin->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="input-group">
                    <label class="input-label">Name</label>
                    <div class="input-field-wrap">
                        <input type="text" name="name" value="{{ old('name', $coin->name) }}" required>
                    </div>
                </div>
                <div class="input-group">
                    <label class="input-label">Ticker</label>
                    <div class="input-field-wrap">
                        <input type="text" name="ticker" value="{{ old('ticker', $coin->ticker) }}" required style="text-transform: uppercase;">
                    </div>
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">CoinGecko ID <span style="color: var(--text-muted); font-weight: 400;">(optional — enables live prices)</span></label>
                <div class="input-field-wrap">
                    <input type="text" name="coingecko_id" value="{{ old('coingecko_id', $coin->coingecko_id ?? '') }}" maxlength="60">
                </div>
                <p style="margin-top: 4px; font-size: 0.75rem; color: var(--text-muted);">
                    When set, the coin's price, market cap, volume and 24h change are pulled live from CoinGecko and simulated auto-movement is disabled.
                </p>
            </div>

            <div class="input-group">
                <label class="input-label">Description</label>
                <div class="input-field-wrap">
                    <textarea name="description" rows="2" style="width: 100%; background: transparent; border: none; color: var(--text-primary); font-family: inherit;">{{ old('description', $coin->description) }}</textarea>
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Logo</label>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <img src="{{ $coin->logo_url }}" alt="{{ $coin->name }}" style="width: 36px; height: 36px; border-radius: 50%;" onerror="this.src='{{ asset('images/coins/pepeking.svg') }}'">
                    <div class="input-field-wrap" style="flex: 1; padding: 6px 12px;">
                        <input type="file" name="logo" accept="image/*" style="font-size: 0.85rem;">
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="input-group">
                    <label class="input-label">Current Price ($)</label>
                    <div class="input-field-wrap">
                        <input type="number" step="any" name="current_price" value="{{ old('current_price', $coin->current_price) }}" required>
                    </div>
                </div>
                <div class="input-group">
                    <label class="input-label">Total Supply (tokens)</label>
                    <div class="input-field-wrap">
                        <input type="number" step="any" name="total_supply" value="{{ old('total_supply', $coin->total_supply) }}" required min="1">
                    </div>
                </div>
            </div>

            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: -8px;">Market cap is computed automatically: price × total supply (currently {{ $coin->formatted_market_cap }}).</p>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                <div class="input-group">
                    <label class="input-label">Holders</label>
                    <div class="input-field-wrap">
                        <input type="number" name="holders_count" value="{{ old('holders_count', $coin->holders_count) }}" required>
                    </div>
                </div>
                <div class="input-group">
                    <label class="input-label">Buyers</label>
                    <div class="input-field-wrap">
                        <input type="number" name="buyers_count" value="{{ old('buyers_count', $coin->buyers_count) }}" required>
                    </div>
                </div>
                <div class="input-group">
                    <label class="input-label">24h Volume ($)</label>
                    <div class="input-field-wrap">
                        <input type="number" step="any" name="volume_24h" value="{{ old('volume_24h', $coin->volume_24h) }}" required>
                    </div>
                </div>
            </div>

            <!-- Auto Price Movement Engine -->
            <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 16px; margin: 16px 0;">
                <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 10px; color: var(--accent-green);">
                    Auto Price Movement Settings
                </h4>
                
                <div class="input-group">
                    <label class="input-label">Movement Mode</label>
                    <select name="price_movement_mode" style="width: 100%; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); padding: 10px; border-radius: var(--radius-sm); font-weight: 600;">
                        <option value="auto_volatile" {{ $coin->price_movement_mode === 'auto_volatile' ? 'selected' : '' }}>Auto Volatile (Oscillating)</option>
                        <option value="auto_up" {{ $coin->price_movement_mode === 'auto_up' ? 'selected' : '' }}>Auto Up (Bullish Pump)</option>
                        <option value="auto_down" {{ $coin->price_movement_mode === 'auto_down' ? 'selected' : '' }}>Auto Down (Bearish Pullback)</option>
                        <option value="manual" {{ $coin->price_movement_mode === 'manual' ? 'selected' : '' }}>Manual (Freeze Automatic Movement)</option>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="input-group" style="margin-bottom: 0;">
                        <label class="input-label">Step % per Tick</label>
                        <div class="input-field-wrap">
                            <input type="number" step="0.1" name="auto_step_percent" value="{{ old('auto_step_percent', $coin->auto_step_percent) }}" required>
                            <span>%</span>
                        </div>
                    </div>
                    <div class="input-group" style="margin-bottom: 0;">
                        <label class="input-label">Interval (Seconds)</label>
                        <div class="input-field-wrap">
                            <input type="number" name="auto_interval_seconds" value="{{ old('auto_interval_seconds', $coin->auto_interval_seconds) }}" required>
                            <span>s</span>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_featured" value="1" {{ $coin->is_featured ? 'checked' : '' }}>
                    <span>Featured on Homepage</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_trending" value="1" {{ $coin->is_trending ? 'checked' : '' }}>
                    <span>Trending Section</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ $coin->is_active ? 'checked' : '' }}>
                    <span>Active (Unpaused)</span>
                </label>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px;">
                    Save All Changes
                </button>
                <a href="{{ route('coins.show', $coin->ticker) }}" target="_blank" class="btn btn-secondary" style="padding: 12px 18px;">
                    View Public Page ↗
                </a>
            </div>
        </form>
    </div>

</div>

@endsection
