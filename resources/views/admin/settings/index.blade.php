@extends('layouts.admin')

@section('title', 'Platform Settings & Fees – Pump Endless Admin')
@section('header_title', 'Platform Settings & Fee Management')

@section('content')

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <!-- Website Branding -->
    <div class="widget-card" style="max-width: 820px; margin: 0 auto 24px;">
        <div style="margin-bottom: 24px;">
            <h3 style="font-size: 1.3rem; font-weight: 800;"><i class="fa-solid fa-palette"></i> Website Branding</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem;">Change the site name, description, logo and favicon icon.</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="input-group">
                <label class="input-label">Website Name</label>
                <div class="input-field-wrap">
                    <input type="text" name="site_name" value="{{ $settings['site_name'] }}" required maxlength="60">
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Tagline / Platform Title</label>
                <div class="input-field-wrap">
                    <input type="text" name="platform_name" value="{{ $settings['platform_name'] }}" required maxlength="50">
                </div>
            </div>
        </div>

        <div class="input-group">
            <label class="input-label">Site Description (meta)</label>
            <div class="input-field-wrap">
                <textarea name="site_description" rows="2" style="width: 100%; background: transparent; border: none; color: var(--text-primary); font-family: inherit;">{{ $settings['site_description'] }}</textarea>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 8px;">
            <div class="input-group">
                <label class="input-label">Website Logo</label>
                <div class="input-field-wrap" style="gap: 10px;">
                    @if($settings['site_logo'])
                        <img src="{{ asset($settings['site_logo']) }}" alt="logo" style="width: 40px; height: 40px; border-radius: 10px; object-fit: cover; border: 1px solid var(--border-color);">
                    @endif
                    <input type="file" name="site_logo" accept="image/png,image/jpeg,image/gif,image/webp">
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">PNG/JPG/GIF/WebP, max 2MB. Leave empty to keep current.</div>
            </div>

            <div class="input-group">
                <label class="input-label">Favicon / Site Icon</label>
                <div class="input-field-wrap" style="gap: 10px;">
                    @if($settings['site_icon'])
                        <img src="{{ asset($settings['site_icon']) }}" alt="icon" style="width: 28px; height: 28px; border-radius: 6px; object-fit: cover; border: 1px solid var(--border-color);">
                    @endif
                    <input type="file" name="site_icon" accept="image/png,image/jpeg,image/gif,image/webp">
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">Shown in the browser tab. PNG recommended (square).</div>
            </div>
        </div>

        <div style="margin-top: 22px;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px; font-size: 1rem;">
                <i class="fa-solid fa-floppy-disk"></i> Save Branding
            </button>
        </div>
    </div>
</form>

<form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf

    <div class="widget-card" style="max-width: 720px; margin: 0 auto;">
        <div style="margin-bottom: 24px;">
            <h3 style="font-size: 1.3rem; font-weight: 800;"><i class="fa-solid fa-sliders"></i> Fees & Simulation Rates</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem;">Adjust platform swap fees, network fees, simulated base rates, and announcement text.</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="input-group">
                <label class="input-label">Meme Coin → BTC Swap Fee (%)</label>
                <div class="input-field-wrap">
                    <input type="number" step="0.01" min="0" max="25" name="swap_fee_percent" value="{{ $settings['swap_fee_percent'] }}" required>
                    <span style="font-weight: 700; color: var(--accent-green);">%</span>
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Bitcoin Withdrawal Fee (BTC)</label>
                <div class="input-field-wrap">
                    <input type="number" step="any" min="0" name="btc_withdrawal_fee" value="{{ $settings['btc_withdrawal_fee'] }}" required>
                    <span style="font-weight: 700; color: #f7931a;">BTC</span>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="input-group">
                <label class="input-label">Fallback Bitcoin Rate ($/BTC)</label>
                <div class="input-field-wrap">
                    <span>$</span>
                    <input type="number" step="10" name="btc_usd_price" value="{{ $settings['btc_usd_price'] }}" required>
                </div>
                <p style="margin-top: 4px; font-size: 0.75rem; color: var(--text-muted);">Used only when the live price feed is unavailable. Live BTC/USD price is fetched automatically.</p>
            </div>

            <div class="input-group">
                <label class="input-label">Fallback Solana Rate ($/SOL)</label>
                <div class="input-field-wrap">
                    <span>$</span>
                    <input type="number" step="0.5" name="sol_usd_price" value="{{ $settings['sol_usd_price'] }}" required>
                </div>
                <p style="margin-top: 4px; font-size: 0.75rem; color: var(--text-muted);">Used only when the live price feed is unavailable.</p>
            </div>
        </div>

        <div class="input-group">
            <label class="input-label">Platform Announcement Message</label>
            <div class="input-field-wrap">
                <textarea name="platform_announcement" rows="2" style="width: 100%; background: transparent; border: none; color: var(--text-primary); font-family: inherit;">{{ $settings['platform_announcement'] }}</textarea>
            </div>
        </div>

        <div style="margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px; font-size: 1rem;">
                <i class="fa-solid fa-floppy-disk"></i> Save Fees & Rates
            </button>
        </div>
    </div>
</form>

@endsection