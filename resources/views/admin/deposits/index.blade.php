@extends('layouts.admin')

@section('title', 'Manage Deposits – Pump Endless Admin')
@section('header_title', 'Deposit Requests & Wallet Addresses')

@section('content')

<!-- Tabs & Filters -->
<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 24px;">
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('admin.deposits.index') }}" class="btn btn-secondary btn-sm {{ empty($status) ? 'btn-primary' : '' }}">
            All Deposits
        </a>
        <a href="{{ route('admin.deposits.index', ['status' => 'pending']) }}" class="btn btn-secondary btn-sm {{ $status === 'pending' ? 'btn-primary' : '' }}">
            Pending ({{ $pendingCount }})
        </a>
        <a href="{{ route('admin.deposits.index', ['status' => 'confirmed']) }}" class="btn btn-secondary btn-sm {{ $status === 'confirmed' ? 'btn-primary' : '' }}">
            Confirmed ({{ $confirmedCount }})
        </a>
        <a href="{{ route('admin.deposits.index', ['status' => 'rejected']) }}" class="btn btn-secondary btn-sm {{ $status === 'rejected' ? 'btn-primary' : '' }}">
            Rejected ({{ $rejectedCount }})
        </a>
    </div>

    <a href="#wallet-config" class="btn btn-secondary btn-sm">
        <i class="fa-solid fa-gear"></i> Configure Deposit Addresses
    </a>
</div>

<!-- Deposits Table -->
<div class="widget-card" style="margin-bottom: 30px;">
    <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 16px;">User Deposit Submissions</h3>

    <div style="overflow-x: auto;">
        <table class="trades-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User / Wallet</th>
                    <th>Amount</th>
                    <th>Currency</th>
                    <th>TXID</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deposits as $dep)
                    <tr>
                        <td style="font-family: var(--font-mono);">#{{ $dep->id }}</td>
                        <td>
                            <div>
                                <a href="{{ route('admin.users.edit', $dep->user_id) }}" style="font-weight: 700;">
                                    {{ $dep->user->name ?? 'Trader' }}
                                </a>
                                <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted);">
                                    {{ $dep->user->wallet_address ?? 'No wallet' }}
                                </div>
                            </div>
                        </td>
                        <td style="font-family: var(--font-mono); font-weight: 700; color: var(--accent-green);">
                            {{ number_format($dep->amount, 4) }}
                        </td>
                        <td style="font-weight: 700;">{{ $dep->currency }}</td>
                        <td style="font-family: var(--font-mono); font-size: 0.85rem;" title="{{ $dep->txid }}">
                            {{ $dep->short_txid }}
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.8rem;">
                            {{ $dep->created_at->format('M d, Y h:i A') }}
                        </td>
                        <td>
                            <span class="badge {{ $dep->status === 'confirmed' ? 'badge-success' : ($dep->status === 'pending' ? 'badge-warning' : 'badge-danger') }}">
                                {{ ucfirst($dep->status) }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                @if($dep->status !== 'confirmed')
                                    <form action="{{ route('admin.deposits.status', $dep->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit" class="btn btn-primary btn-sm" style="padding: 4px 10px; font-size: 0.75rem;" title="Approve & Credit Balance">
                                            <i class="fa-solid fa-check"></i> Confirm & Credit
                                        </button>
                                    </form>
                                @endif

                                @if($dep->status !== 'rejected')
                                    <form action="{{ route('admin.deposits.status', $dep->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Reject deposit request?');">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 8px; font-size: 0.75rem;" title="Reject">
                                            <i class="fa-solid fa-xmark"></i> Reject
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            No deposit submissions found for this filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $deposits->links() }}
    </div>
</div>

<!-- Deposit Wallet Addresses Settings Section -->
<div id="wallet-config" class="widget-card">
    <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 6px;">Deposit Wallet Addresses</h3>
    <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 20px;">
        Configure the official platform wallet addresses and minimum deposits shown to users on the Deposit page.
    </p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
        @foreach($depositMethods as $method)
            <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.4rem;">
                            {{ $method->currency === 'BTC' ? '<i class="fa-solid fa-bitcoin-sign"></i>' : ($method->currency === 'ETH' ? 'Ξ' : ($method->currency === 'SOL' ? '◎' : '₮')) }}
                        </span>
                        <h4 style="font-weight: 800;">{{ $method->name }}</h4>
                    </div>
                    <span class="badge {{ $method->is_active ? 'badge-success' : 'badge-danger' }}">
                        {{ $method->is_active ? 'Active' : 'Disabled' }}
                    </span>
                </div>

                <form action="{{ route('admin.deposit-methods.update', $method->id) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <label class="input-label">Receiving Wallet Address</label>
                        <div class="input-field-wrap">
                            <input type="text" name="wallet_address" value="{{ $method->wallet_address }}" required style="font-size: 0.85rem;">
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">Minimum Deposit</label>
                        <div class="input-field-wrap">
                            <input type="text" name="min_deposit" value="{{ $method->min_deposit }}" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">User Instructions / Notes</label>
                        <div class="input-field-wrap">
                            <input type="text" name="notes" value="{{ $method->notes }}" style="font-size: 0.85rem;">
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 14px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" {{ $method->is_active ? 'checked' : '' }}>
                            <span>Enable Method</span>
                        </label>

                        <button type="submit" class="btn btn-secondary btn-sm" style="border-color: var(--accent-green); color: var(--accent-green);">
                            Save Address
                        </button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</div>

@endsection
