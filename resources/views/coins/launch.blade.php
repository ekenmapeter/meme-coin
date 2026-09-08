@extends('layouts.app')

@section('title', 'Launch a Meme Coin – Pump Endless')

@section('content')
<div class="section" style="max-width: 680px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="font-size: 2.4rem; font-weight: 900; letter-spacing: -0.5px;">Launch a Meme Coin</h1>
        <p style="color: var(--text-secondary); margin-top: 8px;">
            Deploy instantly to our simulated Solana bonding curve. No dev taxes, zero rug pulls, guaranteed fair launch.
        </p>
    </div>

    <div class="widget-card" style="padding: 30px;">
        <form action="{{ route('coins.storeLaunch') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="input-group">
                <label class="input-label">Coin Name</label>
                <div class="input-field-wrap">
                    <input type="text" name="name" placeholder="e.g. PEPEKING" value="{{ old('name') }}" required>
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Ticker Symbol (max 10 chars)</label>
                <div class="input-field-wrap">
                    <input type="text" name="ticker" placeholder="e.g. PEPE" value="{{ old('ticker') }}" maxlength="10" required style="text-transform: uppercase;">
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Description / Meme Lore</label>
                <div class="input-field-wrap">
                    <textarea name="description" rows="3" placeholder="Describe the meme, narrative, and community vibe..." required style="width: 100%; background: transparent; border: none; color: var(--text-primary); font-family: inherit; font-size: 0.95rem; resize: vertical;">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Coin Logo / Icon (Optional)</label>
                <div class="input-field-wrap" style="padding: 8px 14px;">
                    <input type="file" name="logo" accept="image/*" style="font-size: 0.9rem; color: var(--text-secondary);">
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">PNG, JPG, or SVG up to 2MB. Defaults to green Pepe badge if empty.</div>
            </div>

            <div class="input-group">
                <label class="input-label">Initial Liquidity Seed (Demo SOL)</label>
                <div class="input-field-wrap">
                    <input type="number" step="0.1" name="initial_liquidity" value="1.0" min="0.1">
                    <span style="font-weight: 700; color: var(--accent-green);">SOL</span>
                </div>
            </div>

            <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 24px; font-size: 0.85rem; color: var(--text-secondary);">
                💡 <strong>Fair Launch Guarantee:</strong> When the market cap reaches $69,000, $12,000 of liquidity is automatically deposited to simulated Raydium and burned.
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 16px; font-size: 1.05rem; border-radius: var(--radius-md);">
                🚀 Launch Coin On Bonding Curve
            </button>
        </form>
    </div>
</div>
@endsection
