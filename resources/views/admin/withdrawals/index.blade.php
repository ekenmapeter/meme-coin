@extends('layouts.admin')

@section('title', 'Manage Withdrawals – Pump Endless Admin')
@section('header_title', 'Bitcoin Withdrawal Requests')

@section('content')

<!-- Tabs & Filters -->
<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 24px;">
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-secondary btn-sm {{ empty($status) ? 'btn-primary' : '' }}">
            All Withdrawals
        </a>
        <a href="{{ route('admin.withdrawals.index', ['status' => 'pending']) }}" class="btn btn-secondary btn-sm {{ $status === 'pending' ? 'btn-primary' : '' }}">
            Pending ({{ $pendingCount }})
        </a>
        <a href="{{ route('admin.withdrawals.index', ['status' => 'approved']) }}" class="btn btn-secondary btn-sm {{ $status === 'approved' ? 'btn-primary' : '' }}">
            Approved ({{ $approvedCount }})
        </a>
        <a href="{{ route('admin.withdrawals.index', ['status' => 'completed']) }}" class="btn btn-secondary btn-sm {{ $status === 'completed' ? 'btn-primary' : '' }}">
            Completed ({{ $completedCount }})
        </a>
        <a href="{{ route('admin.withdrawals.index', ['status' => 'rejected']) }}" class="btn btn-secondary btn-sm {{ $status === 'rejected' ? 'btn-primary' : '' }}">
            Rejected ({{ $rejectedCount }})
        </a>
    </div>
</div>

<div class="widget-card">
    <div style="overflow-x: auto;">
        <table class="trades-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Requested</th>
                    <th>Network Fee</th>
                    <th>Net Amount</th>
                    <th>Destination BTC Address</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($withdrawals as $w)
                    <tr>
                        <td style="font-family: var(--font-mono);">#{{ $w->id }}</td>
                        <td>
                            <div style="font-weight: 700;">{{ $w->user->name ?? 'Trader' }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $w->user->email ?? '' }}</div>
                        </td>
                        <td style="font-family: var(--font-mono); font-weight: 700;">
                            {{ sprintf('%.6f', $w->amount) }} BTC
                        </td>
                        <td style="font-family: var(--font-mono); color: var(--text-muted); font-size: 0.85rem;">
                            {{ sprintf('%.6f', $w->network_fee) }} BTC
                        </td>
                        <td style="font-family: var(--font-mono); font-weight: 800; color: var(--accent-green);">
                            {{ sprintf('%.6f', $w->net_amount) }} BTC
                        </td>
                        <td style="font-family: var(--font-mono); font-size: 0.85rem;" title="{{ $w->destination_address }}">
                            {{ $w->short_address }}
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $w->destination_address }}'); alert('Address copied!');" style="background:none; border:none; color:var(--text-muted); cursor:pointer;">⎘</button>
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.8rem;">
                            {{ $w->created_at->format('M d, Y h:i A') }}
                        </td>
                        <td>
                            <span class="badge {{ $w->status === 'completed' || $w->status === 'approved' ? 'badge-success' : ($w->status === 'pending' ? 'badge-warning' : 'badge-danger') }}">
                                {{ ucfirst($w->status) }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px; align-items: flex-start;">
                                @if($w->status === 'pending')
                                    <form action="{{ route('admin.withdrawals.status', $w->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-primary btn-sm" style="padding: 4px 10px; font-size: 0.75rem;">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.withdrawals.status', $w->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Reject and refund BTC balance?');">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 8px; font-size: 0.75rem;">
                                            <i class="fa-solid fa-xmark"></i> Reject & Refund
                                        </button>
                                    </form>
                                @elseif($w->status === 'approved')
                                    <form action="{{ route('admin.withdrawals.status', $w->id) }}" method="POST" style="display: flex; gap: 6px; align-items: center;">
                                        @csrf
                                        <input type="hidden" name="status" value="completed">
                                        <input type="text" name="txid" placeholder="Broadcast txid (required)" required
                                               style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 6px 10px; font-size: 0.75rem; font-family: var(--font-mono); width: 170px;">
                                        <button type="submit" class="btn btn-primary btn-sm" style="padding: 4px 8px; font-size: 0.75rem; white-space: nowrap;">
                                            Mark Completed
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.withdrawals.status', $w->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Reject and refund BTC balance?');">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 8px; font-size: 0.75rem;">
                                            <i class="fa-solid fa-xmark"></i> Reject & Refund
                                        </button>
                                    </form>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">
                                        {{ $w->txid ? 'Tx: ' . substr($w->txid, 0, 12) . '…' : 'Processed' }}
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            No withdrawal requests found for this filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $withdrawals->links() }}
    </div>
</div>

@endsection
