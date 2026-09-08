@extends('layouts.admin')

@section('title', "Edit User: {$user->name} – Pump Endless Admin")
@section('header_title', "User Account: {$user->name}")

@section('content')

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    
    <!-- Balance Adjustment Form -->
    <div class="widget-card">
        <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 16px;">Adjust User Balances</h3>

        <form action="{{ route('admin.users.balance', $user->id) }}" method="POST">
            @csrf

            <div class="input-group">
                <label class="input-label">Bitcoin Balance (BTC)</label>
                <div class="input-field-wrap">
                    <input type="number" step="any" min="0" name="btc_balance" value="{{ $user->btc_balance }}" required>
                    <span style="font-weight: 700; color: #f7931a;">BTC</span>
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Solana Balance (SOL)</label>
                <div class="input-field-wrap">
                    <input type="number" step="any" min="0" name="sol_balance" value="{{ $user->sol_balance }}" required>
                    <span style="font-weight: 700; color: #14f195;">SOL</span>
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">USD Cash Balance ($)</label>
                <div class="input-field-wrap">
                    <span>$</span>
                    <input type="number" step="any" min="0" name="usd_balance" value="{{ $user->usd_balance }}" required>
                </div>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px;">
                    Update User Balances
                </button>
            </div>
        </form>
    </div>

    <!-- User Portfolio Holdings -->
    <div class="widget-card">
        <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 16px;">Current Token Holdings</h3>

        <table class="trades-table">
            <thead>
                <tr>
                    <th>Coin</th>
                    <th>Token Balance</th>
                    <th>Est. USD Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($user->holdings as $holding)
                    <tr>
                        <td>
                            <div style="font-weight: 700;">{{ $holding->coin->name ?? 'Coin' }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $holding->coin->ticker ?? '' }}</div>
                        </td>
                        <td style="font-family: var(--font-mono); font-weight: 700;">
                            {{ number_format($holding->token_balance) }}
                        </td>
                        <td style="font-family: var(--font-mono); color: var(--accent-green);">
                            ${{ number_format($holding->current_value_usd, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 20px;">
                            No meme token holdings.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection
