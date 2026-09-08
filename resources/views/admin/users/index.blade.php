@extends('layouts.admin')

@section('title', 'Manage Users – Pump Endless Admin')
@section('header_title', 'User Accounts & Balances')

@section('content')

<div class="widget-card">
    <div style="overflow-x: auto;">
        <table class="trades-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Wallet Address</th>
                    <th>BTC Balance</th>
                    <th>SOL Balance</th>
                    <th>USD Balance</th>
                    <th>Role</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                        <td>
                            <div style="font-weight: 700;">{{ $u->name }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $u->email }}</div>
                        </td>
                        <td style="font-family: var(--font-mono); font-size: 0.85rem;">
                            {{ $u->wallet_address ? substr($u->wallet_address, 0, 8) . '...' . substr($u->wallet_address, -6) : 'Not assigned' }}
                        </td>
                        <td style="font-family: var(--font-mono); font-weight: 700; color: #f7931a;">
                            {{ sprintf('%.6f', $u->btc_balance) }} BTC
                        </td>
                        <td style="font-family: var(--font-mono); font-weight: 700; color: #14f195;">
                            {{ number_format($u->sol_balance, 4) }} SOL
                        </td>
                        <td style="font-family: var(--font-mono);">
                            ${{ number_format($u->usd_balance, 2) }}
                        </td>
                        <td>
                            <span class="badge {{ $u->isAdmin() ? 'badge-danger' : 'badge-info' }}">
                                {{ $u->isAdmin() ? 'Admin' : 'Trader' }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 0.75rem;">
                                Edit Balances
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $users->links() }}
    </div>
</div>

@endsection
