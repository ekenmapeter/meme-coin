@extends('layouts.admin')

@section('title', "Edit User: {$user->name} – Pump Endless Admin")
@section('header_title', "User Account: {$user->name}")

@section('content')

@if($user->isSuspended())
    <div class="alert alert-error">
        <span><i class="fa-solid fa-ban"></i> This account is currently <strong>SUSPENDED</strong>. The user cannot sign in.</span>
    </div>
@elseif($user->isRestricted())
    <div class="alert alert-warning">
        <span><i class="fa-solid fa-lock"></i> This account is <strong>RESTRICTED</strong> from trading and swapping.</span>
    </div>
@endif

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Account Details & Role -->
    <div class="widget-card">
        <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 16px;"><i class="fa-solid fa-user"></i> Account Details & Role</h3>

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf

            <div class="input-group">
                <label class="input-label">Display Name</label>
                <div class="input-field-wrap">
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="50">
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Email Address</label>
                <div class="input-field-wrap">
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255">
                </div>
            </div>

            <div class="input-group">
                <label class="input-label">Role</label>
                <div class="input-field-wrap">
                    <select name="role" {{ $user->is(Auth::user()) ? 'disabled' : '' }}>
                        <option value="{{ \App\Models\User::ROLE_USER }}" {{ $user->role === \App\Models\User::ROLE_USER ? 'selected' : '' }}>Trader (User)</option>
                        <option value="{{ \App\Models\User::ROLE_ADMIN }}" {{ $user->role === \App\Models\User::ROLE_ADMIN ? 'selected' : '' }}>Administrator</option>
                    </select>
                </div>
                @if($user->is(Auth::user()))
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">You cannot change your own role.</div>
                @endif
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Account
                </button>
            </div>
        </form>
    </div>

    <!-- Account Status & Danger Zone -->
    <div class="widget-card">
        <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 16px;"><i class="fa-solid fa-shield-halved"></i> Account Status</h3>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                <div>
                    <div style="font-weight: 700;">Suspended</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Blocks the user from signing in.</div>
                </div>
                <form action="{{ route('admin.users.suspend', $user->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn {{ $user->isSuspended() ? 'btn-success' : 'btn-danger' }} btn-sm">
                        <i class="fa-solid {{ $user->isSuspended() ? 'fa-user-check' : 'fa-user-slash' }}"></i>
                        {{ $user->isSuspended() ? 'Unsuspend' : 'Suspend' }}
                    </button>
                </form>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                <div>
                    <div style="font-weight: 700;">Restrict Trading</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Blocks buying, selling and swapping.</div>
                </div>
                <form action="{{ route('admin.users.restrict', $user->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <i class="fa-solid {{ $user->isRestricted() ? 'fa-lock-open' : 'fa-lock' }}"></i>
                        {{ $user->isRestricted() ? 'Unrestrict' : 'Restrict' }}
                    </button>
                </form>
            </div>

            @unless($user->is(Auth::user()))
                <div style="padding: 14px; background: rgba(255,59,105,0.06); border: 1px solid rgba(255,59,105,0.25); border-radius: var(--radius-md);">
                    <div style="font-weight: 700; color: var(--accent-red); margin-bottom: 4px;">Danger Zone</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">
                        Permanently deletes the user and all holdings, deposits, withdrawals and swaps.
                    </div>
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Permanently delete {{ $user->name }} and all their data? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-trash"></i> Delete User
                        </button>
                    </form>
                </div>
            @endunless
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 24px;">
    <!-- Balance Adjustment Form -->
    <div class="widget-card">
        <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 16px;"><i class="fa-solid fa-scale-balanced"></i> Adjust User Balances</h3>

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
                    <i class="fa-solid fa-wallet"></i> Update Balances
                </button>
            </div>
        </form>
    </div>

    <!-- User Portfolio Holdings -->
    <div class="widget-card">
        <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 16px;"><i class="fa-solid fa-coins"></i> Current Token Holdings</h3>

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

<div class="widget-card" style="margin-top: 24px;">
        <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 16px;">
            <i class="fa-solid fa-clock-rotate-left"></i> User Activity
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; align-items: start;">
            <!-- Deposits -->
            <div>
                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--accent-green); margin-bottom: 10px;">
                    <i class="fa-solid fa-arrow-down-to-line"></i> Deposits
                </h4>
                <table class="trades-table">
                    <thead><tr><th>Amount</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($user->deposits as $d)
                            <tr>
                                <td style="font-family: var(--font-mono);">{{ number_format($d->amount, 4) }} {{ $d->currency }}</td>
                                <td><span class="badge {{ $d->status === 'confirmed' ? 'badge-success' : ($d->status === 'pending' ? 'badge-warning' : 'badge-danger') }}">{{ ucfirst($d->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" style="color: var(--text-muted); font-size: 0.8rem; text-align: center; padding: 12px;">None</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Withdrawals -->
            <div>
                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--accent-red); margin-bottom: 10px;">
                    <i class="fa-solid fa-arrow-up-from-line"></i> Withdrawals
                </h4>
                <table class="trades-table">
                    <thead><tr><th>Amount</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($user->withdrawals as $w)
                            <tr>
                                <td style="font-family: var(--font-mono);">{{ number_format($w->amount, 4) }} {{ $w->currency }}</td>
                                <td><span class="badge {{ $w->status === 'completed' || $w->status === 'approved' ? 'badge-success' : ($w->status === 'pending' ? 'badge-warning' : 'badge-danger') }}">{{ ucfirst($w->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" style="color: var(--text-muted); font-size: 0.8rem; text-align: center; padding: 12px;">None</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Swaps -->
            <div>
                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--accent-blue); margin-bottom: 10px;">
                    <i class="fa-solid fa-arrow-right-arrow-left"></i> Swaps
                </h4>
                <table class="trades-table">
                    <thead><tr><th>Tokens</th><th>Received</th></tr></thead>
                    <tbody>
                        @forelse($user->swaps as $s)
                            <tr>
                                <td style="font-family: var(--font-mono); font-size: 0.85rem;">{{ number_format($s->token_amount) }} {{ $s->coin->ticker ?? '' }}</td>
                                <td style="font-family: var(--font-mono); font-size: 0.85rem;">{{ sprintf('%.6f', $s->net_btc_received) }} BTC</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" style="color: var(--text-muted); font-size: 0.8rem; text-align: center; padding: 12px;">None</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection