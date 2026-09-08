@extends('layouts.admin')

@section('title', 'Platform Settings & Fees – Pump Endless Admin')
@section('header_title', 'Platform Settings & Fee Management')

@section('content')

<div class="widget-card" style="max-width: 720px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <h3 style="font-size: 1.3rem; font-weight: 800;">Global Platform Configuration</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem;">Adjust platform swap fees, network fees, simulated base rates, and announcement text.</p>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf

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
                <label class="input-label">Simulated Bitcoin Rate ($/BTC)</label>
                <div class="input-field-wrap">
                    <span>$</span>
                    <input type="number" step="10" name="btc_usd_price" value="{{ $settings['btc_usd_price'] }}" required>
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Simulated Solana Rate ($/SOL)</label>
                <div class="input-field-wrap">
                    <span>$</span>
                    <input type="number" step="0.5" name="sol_usd_price" value="{{ $settings['sol_usd_price'] }}" required>
                </div>
            </div>
        </div>

        <div class="input-group">
            <label class="input-label">Platform Title / Brand</label>
            <div class="input-field-wrap">
                <input type="text" name="platform_name" value="{{ $settings['platform_name'] }}" required>
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
                Save Platform Settings
            </button>
        </div>
    </form>
</div>

@endsection
