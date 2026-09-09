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
                    <th>Status</th>
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
                        <td>
                            @if($u->isSuspended())
                                <span class="badge badge-danger"><i class="fa-solid fa-ban"></i> Suspended</span>
                            @elseif($u->isRestricted())
                                <span class="badge badge-warning"><i class="fa-solid fa-lock"></i> Restricted</span>
                            @else
                                <span class="badge badge-success">Active</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end;">
                                <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 0.75rem;">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                @unless($u->is(Auth::user()))
                                    <form action="{{ route('admin.users.suspend', $u->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn {{ $u->isSuspended() ? 'btn-success' : 'btn-danger' }} btn-sm" style="padding: 4px 8px; font-size: 0.75rem;" title="{{ $u->isSuspended() ? 'Unsuspend' : 'Suspend' }}">
                                            <i class="fa-solid {{ $u->isSuspended() ? 'fa-user-check' : 'fa-user-slash' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.restrict', $u->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 0.75rem;" title="{{ $u->isRestricted() ? 'Unrestrict' : 'Restrict trading' }}">
                                            <i class="fa-solid {{ $u->isRestricted() ? 'fa-lock-open' : 'fa-lock' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Permanently delete {{ $u->name }} and all their data? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 8px; font-size: 0.75rem;" title="Delete user">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endunless
                            </div>
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
