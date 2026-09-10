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
                    <tr
                        data-id="{{ $dep->id }}"
                        data-user="{{ $dep->user->name ?? 'Trader' }}"
                        data-wallet="{{ $dep->user->wallet_address ?? 'No wallet' }}"
                        data-method="{{ $dep->depositMethod->name ?? $dep->currency }}"
                        data-amount="{{ number_format($dep->amount, 8, '.', '') }}"
                        data-currency="{{ $dep->currency }}"
                        data-txid="{{ $dep->txid }}"
                        data-date="{{ $dep->created_at->format('M d, Y h:i A') }}"
                        data-status="{{ ucfirst($dep->status) }}"
                        data-notes="{{ $dep->admin_notes ?? '' }}"
                    >
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
                        <td style="font-family: var(--font-mono); font-size: 0.85rem;">
                            <button type="button" onclick="openDepositModal(this.closest('tr'))" title="Click to view full transaction hash"
                                    style="background:none; border:none; color:var(--accent-green); cursor:pointer; font-family:inherit; padding:0;">
                                {{ $dep->short_txid }} <i class="fa-solid fa-up-right-from-square" style="font-size: 0.65rem;"></i>
                            </button>
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
                                <button type="button" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 0.75rem;"
                                        onclick="openDepositModal(this.closest('tr'))" title="View full deposit details & transaction hash">
                                    <i class="fa-solid fa-eye"></i> View
                                </button>

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

<!-- Deposit Details Modal -->
<div id="depositModal" style="display:none; position:fixed; inset:0; z-index:1000; background:rgba(0,0,0,0.65); align-items:center; justify-content:center; padding:20px;" onclick="if (event.target === this) closeDepositModal()">
    <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); max-width:580px; width:100%; padding:24px; max-height:90vh; overflow-y:auto;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px;">
            <h3 style="font-size:1.2rem; font-weight:800;">Deposit <span id="mDepositId" style="font-family:var(--font-mono);"></span></h3>
            <button type="button" onclick="closeDepositModal()" style="background:none; border:none; color:var(--text-muted); font-size:1.2rem; cursor:pointer;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div style="display:flex; flex-direction:column; gap:12px; font-size:0.9rem;">
            <div style="display:flex; justify-content:space-between; gap:12px;">
                <span style="color:var(--text-muted);">User</span>
                <span id="mDepositUser" style="font-weight:700; text-align:right;"></span>
            </div>
            <div style="display:flex; justify-content:space-between; gap:12px;">
                <span style="color:var(--text-muted);">Wallet</span>
                <span id="mDepositWallet" style="font-family:var(--font-mono); font-size:0.8rem; text-align:right; word-break:break-all;"></span>
            </div>
            <div style="display:flex; justify-content:space-between; gap:12px;">
                <span style="color:var(--text-muted);">Method</span>
                <span id="mDepositMethod" style="font-weight:700;"></span>
            </div>
            <div style="display:flex; justify-content:space-between; gap:12px;">
                <span style="color:var(--text-muted);">Amount</span>
                <span id="mDepositAmount" style="font-family:var(--font-mono); font-weight:700; color:var(--accent-green);"></span>
            </div>
            <div style="display:flex; justify-content:space-between; gap:12px;">
                <span style="color:var(--text-muted);">Submitted</span>
                <span id="mDepositDate" style="color:var(--text-secondary);"></span>
            </div>
            <div style="display:flex; justify-content:space-between; gap:12px;">
                <span style="color:var(--text-muted);">Status</span>
                <span id="mDepositStatus"></span>
            </div>

            <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); padding:14px 16px; margin-top:4px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                    <span style="color:var(--text-muted); font-weight:600;">Transaction Hash (TXID)</span>
                    <div style="display:flex; gap:8px;">
                        <button type="button" onclick="copyDepositTxid()" class="btn btn-secondary btn-sm" style="padding:3px 8px; font-size:0.72rem;">⎘ Copy</button>
                        <a id="mTxExplorer" href="#" target="_blank" rel="noopener" class="btn btn-secondary btn-sm" style="padding:3px 8px; font-size:0.72rem; text-decoration:none;">
                            View on Explorer <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:0.65rem;"></i>
                        </a>
                    </div>
                </div>
                <div id="mDepositTxid" style="font-family:var(--font-mono); font-size:0.78rem; color:var(--text-secondary); word-break:break-all; line-height:1.5;"></div>
            </div>

            <div id="mDepositNotesWrap" style="display:none; background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); padding:14px 16px;">
                <div style="color:var(--text-muted); font-weight:600; margin-bottom:6px;">Admin Notes</div>
                <div id="mDepositNotes" style="font-size:0.85rem; color:var(--text-secondary); word-break:break-word;"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openDepositModal(row) {
        const d = row.dataset;

        document.getElementById('mDepositId').textContent = '#' + d.id;
        document.getElementById('mDepositUser').textContent = d.user;
        document.getElementById('mDepositWallet').textContent = d.wallet;
        document.getElementById('mDepositMethod').textContent = d.method;
        document.getElementById('mDepositAmount').textContent = Number(d.amount).toLocaleString(undefined, { maximumFractionDigits: 8 }) + ' ' + d.currency;
        document.getElementById('mDepositDate').textContent = d.date;
        document.getElementById('mDepositStatus').innerHTML = '<span class="badge ' + (d.status === 'Confirmed' ? 'badge-success' : (d.status === 'Pending' ? 'badge-warning' : 'badge-danger')) + '">' + d.status + '</span>';
        document.getElementById('mDepositTxid').textContent = d.txid;

        const explorer = explorerUrl(d.currency, d.txid);
        const explorerLink = document.getElementById('mTxExplorer');
        explorerLink.style.display = explorer ? '' : 'none';
        if (explorer) {
            explorerLink.href = explorer;
        }

        const notes = d.notes.trim();
        const notesWrap = document.getElementById('mDepositNotesWrap');
        notesWrap.style.display = notes ? '' : 'none';
        document.getElementById('mDepositNotes').textContent = notes;

        const modal = document.getElementById('depositModal');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeDepositModal() {
        document.getElementById('depositModal').style.display = 'none';
        document.body.style.overflow = '';
    }

    function copyDepositTxid() {
        const txid = document.getElementById('mDepositTxid').textContent.trim();
        navigator.clipboard.writeText(txid).then(() => alert('Transaction hash copied: ' + txid));
    }

    function explorerUrl(currency, txid) {
        if (!txid) return null;
        const tx = encodeURIComponent(txid);
        if (currency === 'BTC') return 'https://mempool.space/tx/' + tx;
        if (currency === 'SOL') return 'https://solscan.io/tx/' + tx;
        if (currency === 'ETH') return 'https://etherscan.io/tx/' + tx;
        return null;
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeDepositModal();
    });
</script>
@endpush
@endsection
