@extends('layouts.admin')

@section('title', 'Create New Coin – Pump Endless Admin')
@section('header_title', 'Create Meme Coin')

@section('content')

<div class="widget-card" style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <h3 style="font-size: 1.3rem; font-weight: 800;">Deploy New Meme Coin</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem;">All parameters are fully controllable from your admin panel.</p>
    </div>

    <form action="{{ route('admin.coins.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="input-group">
                <label class="input-label">Coin Name</label>
                <div class="input-field-wrap">
                    <input type="text" name="name" placeholder="e.g. PEPEKING" value="{{ old('name') }}" required>
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Ticker Symbol</label>
                <div class="input-field-wrap">
                    <input type="text" name="ticker" placeholder="e.g. PEPE" value="{{ old('ticker') }}" maxlength="10" required style="text-transform: uppercase;">
                </div>
            </div>
        </div>

        <div class="input-group">
            <label class="input-label">CoinGecko ID <span style="color: var(--text-muted); font-weight: 400;">(optional — enables live prices)</span></label>
            <div class="input-field-wrap">
                <input type="text" name="coingecko_id" placeholder="e.g. pepe, dogecoin, shiba-inu" value="{{ old('coingecko_id') }}" maxlength="60">
            </div>
            <p style="margin-top: 4px; font-size: 0.75rem; color: var(--text-muted);">
                When set, the coin's price, market cap, volume and 24h change are pulled live from CoinGecko and auto-movement is disabled.
            </p>
        </div>

        <div class="input-group">
            <label class="input-label">Description / Lore</label>
            <div class="input-field-wrap">
                <textarea name="description" rows="3" placeholder="Enter coin description..." style="width: 100%; background: transparent; border: none; color: var(--text-primary); font-family: inherit; font-size: 0.95rem;">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="input-group">
            <label class="input-label">Coin Logo</label>
            <div class="input-field-wrap" style="padding: 8px 14px;">
                <input type="file" name="logo" accept="image/*">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="input-group">
                <label class="input-label">Initial Price (USD)</label>
                <div class="input-field-wrap">
                    <input type="number" step="any" name="current_price" value="{{ old('current_price', '0.000245') }}" required>
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Total Supply (tokens)</label>
                <div class="input-field-wrap">
                    <input type="number" step="any" name="total_supply" value="{{ old('total_supply', '10000000000') }}" required min="1">
                </div>
            </div>
        </div>

        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: -8px;">Market cap is computed automatically: price × total supply.</p>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div class="input-group">
                <label class="input-label">Holders Count</label>
                <div class="input-field-wrap">
                    <input type="number" name="holders_count" value="{{ old('holders_count', '12652') }}" required>
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Buyers Count</label>
                <div class="input-field-wrap">
                    <input type="number" name="buyers_count" value="{{ old('buyers_count', '14231') }}" required>
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">24h Volume (USD)</label>
                <div class="input-field-wrap">
                    <input type="number" step="any" name="volume_24h" value="{{ old('volume_24h', '1245230') }}" required>
                </div>
            </div>
        </div>

        <!-- Simulation Controls -->
        <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; margin: 16px 0 24px;">
            <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 12px; color: var(--accent-green);"><i class="fa-solid fa-robot"></i> Automatic Price Simulation Engine</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="input-group" style="margin-bottom: 0;">
                    <label class="input-label">Price Movement Mode</label>
                    <select name="price_movement_mode" style="width: 100%; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); padding: 10px; border-radius: var(--radius-sm); font-weight: 600;">
                        <option value="auto_volatile" selected>Auto Volatile (Oscillating)</option>
                        <option value="auto_up">Auto Up (Bullish Pump)</option>
                        <option value="auto_down">Auto Down (Bearish Pullback)</option>
                        <option value="manual">Manual Only</option>
                    </select>
                </div>

                <div class="input-group" style="margin-bottom: 0;">
                    <label class="input-label">Step % per Tick</label>
                    <div class="input-field-wrap">
                        <input type="number" step="0.1" name="auto_step_percent" value="1.50" required>
                        <span>%</span>
                    </div>
                </div>

                <div class="input-group" style="margin-bottom: 0;">
                    <label class="input-label">Tick Interval (Seconds)</label>
                    <div class="input-field-wrap">
                        <input type="number" name="auto_interval_seconds" value="10" required>
                        <span>s</span>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 24px;">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <input type="checkbox" name="is_featured" value="1">
                <span>Feature on Homepage</span>
            </label>
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <input type="checkbox" name="is_trending" value="1" checked>
                <span>Add to Trending</span>
            </label>
        </div>

        <div style="display: flex; gap: 12px;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 28px;">
                Save and Deploy Coin
            </button>
            <a href="{{ route('admin.coins.index') }}" class="btn btn-secondary" style="padding: 12px 24px;">
                Cancel
            </a>
        </div>
    </form>
</div>

@endsection
