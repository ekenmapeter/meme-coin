@extends('layouts.app')

@section('title', 'Wallet, Swap & Transfers – Pump Endless')

@section('content')
<div class="section" style="padding-top: 20px;">

    <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 24px; align-items: start; margin-bottom: 24px;">
        
        <!-- Left Column: Total Balance Card & Swap Widget -->
        <div>
            <!-- Total Balance Card matching wallet page.png -->
            <div class="widget-card" style="margin-bottom: 20px;">
                <div class="stat-label" style="font-size: 0.85rem; margin-bottom: 4px;">Wallet</div>
                <div style="color: var(--text-muted); font-size: 0.9rem;">Total Balance</div>
                <div style="font-size: 2.2rem; font-weight: 900; font-family: var(--font-mono); margin: 6px 0;">
                    {{ sprintf('%.6f', $totalBalanceBtc) }} BTC
                </div>
                <div style="color: var(--text-secondary); font-size: 1.05rem; font-family: var(--font-mono); margin-bottom: 20px;">
                    ≈ ${{ number_format($totalValuationUsd, 2) }}
                </div>

                <div style="display: flex; gap: 12px;">
                    <button type="button" class="btn btn-primary" onclick="setRightPanelTab('deposit')" style="padding: 10px 24px;">
                        Deposit
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="setRightPanelTab('withdraw')" style="padding: 10px 24px;">
                        Withdraw
                    </button>
                </div>
            </div>

            <!-- Swap Card matching wallet page.png -->
            <div class="widget-card">
                <h3 style="font-size: 1.3rem; font-weight: 800; margin-bottom: 18px;">Swap</h3>

                <form action="{{ route('wallet.swap') }}" method="POST" id="swapForm">
                    @csrf

                    <!-- From Input -->
                    <div class="input-group">
                        <div class="input-label">
                            <span>From</span>
                            <span>Balance: <strong id="swapFromBalance" style="color: var(--text-primary); font-family: var(--font-mono);">
                                @php
                                    $firstHolding = $holdings->first();
                                @endphp
                                {{ $firstHolding ? number_format($firstHolding->token_balance) : '0' }}
                            </strong></span>
                        </div>
                        <div class="input-field-wrap">
                            <select name="coin_id" id="swapCoinSelect" onchange="handleSwapCoinChange()" style="background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary); padding: 8px 12px; border-radius: var(--radius-sm); font-weight: 700;">
                                @foreach($allCoins as $coin)
                                    @php
                                        $userH = $holdings->firstWhere('coin_id', $coin->id);
                                        $bal = $userH ? $userH->token_balance : 0;
                                    @endphp
                                    <option value="{{ $coin->id }}" data-balance="{{ $bal }}" data-price="{{ $coin->current_price }}" data-ticker="{{ $coin->ticker }}" {{ ($firstHolding && $firstHolding->coin_id == $coin->id) ? 'selected' : '' }}>
                                        {{ $coin->name }} ({{ $coin->ticker }})
                                    </option>
                                @endforeach
                            </select>

                            <input type="number" step="any" name="token_amount" id="swapTokenAmount" placeholder="1000000" oninput="calculateSwapPreview()" required style="text-align: right;">
                        </div>
                    </div>

                    <!-- Swap Direction Divider -->
                    <div style="text-align: center; margin: -6px 0 10px; color: var(--accent-green); font-size: 1.2rem;">
                        ⇅
                    </div>

                    <!-- To Input (Bitcoin) -->
                    <div class="input-group">
                        <div class="input-label">
                            <span>To</span>
                            <span>Est. Price: <strong style="color: var(--text-primary); font-family: var(--font-mono);">${{ number_format($btcPriceUsd, 0) }}</strong></span>
                        </div>
                        <div class="input-field-wrap" style="background: rgba(255,255,255,0.02);">
                            <div style="display: flex; align-items: center; gap: 8px; font-weight: 700;">
                                <span style="font-size: 1.2rem;">₿</span>
                                <span>Bitcoin (BTC)</span>
                            </div>
                            <input type="text" id="swapBtcOutput" placeholder="0.015212" readonly style="color: var(--accent-green); text-align: right;">
                        </div>
                    </div>

                    <!-- Details: Rate, Swap Fee, You Will Receive matching wallet page.png -->
                    <div style="background: var(--bg-secondary); border-radius: var(--radius-sm); padding: 14px 16px; margin-bottom: 20px; font-size: 0.88rem; display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Rate</span>
                            <span id="swapRateDisplay" style="font-family: var(--font-mono); font-weight: 600;">1 TOKEN = 0.0000000152 BTC</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Swap Fee ({{ $swapFeePercent }}%)</span>
                            <span id="swapFeeDisplay" style="font-family: var(--font-mono); color: var(--text-secondary);">0.000000152 BTC</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 8px;">
                            <span style="font-weight: 600;">You Will Receive</span>
                            <span id="swapNetDisplay" style="font-family: var(--font-mono); font-weight: 800; color: var(--accent-green); font-size: 1rem;">
                                0.015060 BTC
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1.05rem; border-radius: var(--radius-md);">
                        Swap Now
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column: Interactive Tabs for Withdrawal / Deposit -->
        <div>
            <div class="widget-card">
                <div class="widget-tabs" style="margin-bottom: 24px;">
                    <button type="button" class="widget-tab active" id="tabNavDeposit" onclick="setRightPanelTab('deposit')">Deposit</button>
                    <button type="button" class="widget-tab" id="tabNavWithdraw" onclick="setRightPanelTab('withdraw')">Withdraw</button>
                </div>

                <!-- 1. Deposit Panel matching deposit withdrawal.png -->
                <div id="panelDeposit">
                    <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 16px;">Deposit Address</h3>

                    <!-- Method Selector -->
                    <div class="input-group">
                        <select id="depositMethodSelect" onchange="handleDepositMethodChange()" style="width: 100%; background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px 14px; border-radius: var(--radius-sm); font-weight: 700; font-size: 0.95rem;">
                            @foreach($depositMethods as $dm)
                                <option value="{{ $dm->id }}" data-currency="{{ $dm->currency }}" data-address="{{ $dm->wallet_address }}" data-min="{{ $dm->min_deposit }}" data-notes="{{ $dm->notes }}" data-confirmations="{{ $dm->confirmations_required }}">
                                    {{ $dm->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- QR Code & Address Display matching deposit withdrawal.png -->
                    <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; text-align: center; margin-bottom: 20px;">
                        <div id="depositQrContainer" style="display: inline-block; background: #fff; padding: 12px; border-radius: 8px; margin-bottom: 16px;"></div>

                        <div class="input-label" style="text-align: left;">Wallet Address</div>
                        <div class="input-field-wrap" style="background: var(--bg-input);">
                            <input type="text" id="depositAddressField" value="{{ $defaultDepositMethod->wallet_address ?? '' }}" readonly style="font-size: 0.85rem;">
                            <button type="button" onclick="copyDepositAddress()" class="btn btn-secondary btn-sm" style="padding: 4px 10px;" title="Copy Address">
                                ⎘ Copy
                            </button>
                        </div>

                        <div id="depositInstructions" style="color: var(--text-muted); font-size: 0.8rem; text-align: left; margin-top: 14px; line-height: 1.5;">
                            {{ $defaultDepositMethod->notes ?? 'Send only funds to this address. Minimum deposit: 0.001. Deposits will be credited after confirmations.' }}
                        </div>
                    </div>

                    <!-- Submit Deposit Form -->
                    <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 12px;">Submit Deposit Confirmation</h4>
                    <form action="{{ route('wallet.deposit') }}" method="POST">
                        @csrf
                        <input type="hidden" name="deposit_method_id" id="hiddenDepositMethodId" value="{{ $defaultDepositMethod->id ?? 1 }}">

                        <div class="input-group">
                            <label class="input-label">Deposited Amount</label>
                            <div class="input-field-wrap">
                                <input type="number" step="any" min="0.0001" name="amount" placeholder="0.025" required>
                                <span id="depositCurrencyLabel" style="font-weight: 700; color: var(--accent-green);">BTC</span>
                            </div>
                        </div>

                        <div class="input-group">
                            <label class="input-label">Transaction Hash / TXID</label>
                            <div class="input-field-wrap">
                                <input type="text" name="txid" placeholder="Paste transaction hash here..." required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; border-radius: var(--radius-md);">
                            Submit Deposit for Verification
                        </button>
                    </form>
                </div>

                <!-- 2. Withdrawal Panel matching wallet page.png -->
                <div id="panelWithdraw" style="display: none;">
                    <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 16px;">Withdrawal</h3>

                    <div style="background: var(--bg-secondary); border-radius: var(--radius-sm); padding: 14px 16px; margin-bottom: 20px; font-size: 0.88rem; display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Bitcoin Balance</span>
                            <span style="font-family: var(--font-mono); font-weight: 700;">{{ sprintf('%.6f', $user->btc_balance) }} BTC</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Network Fee</span>
                            <span style="font-family: var(--font-mono); color: var(--text-secondary);">{{ sprintf('%.6f', $withdrawalNetworkFee) }} BTC</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 8px;">
                            <span style="font-weight: 600;">You Will Receive</span>
                            <span id="withdrawNetDisplay" style="font-family: var(--font-mono); font-weight: 800; color: var(--accent-green);">
                                {{ sprintf('%.6f', max(0, $user->btc_balance - $withdrawalNetworkFee)) }} BTC
                            </span>
                        </div>
                    </div>

                    <form action="{{ route('wallet.withdraw') }}" method="POST" onsubmit="return validateWithdrawal(event)">
                        @csrf
                        <div class="input-group">
                            <label class="input-label">Bitcoin Address</label>
                            <div class="input-field-wrap">
                                <input type="text" name="destination_address" placeholder="bc1qxy2kgdygjrsztqz2n0yrf2493p831kkfjhx0wlh" value="bc1qxy2kgdygjrsztqz2n0yrf2493p831kkfjhx0wlh" required style="font-size: 0.85rem;">
                            </div>
                        </div>

                        <div class="input-group">
                            <div class="input-label">
                                <span>Amount (BTC)</span>
                                <button type="button" onclick="setMaxWithdrawal()" style="background:none; border:none; color:var(--accent-green); cursor:pointer; font-weight:600;">MAX</button>
                            </div>
                            <div class="input-field-wrap">
                                <input type="number" step="any" min="0.0004" name="amount" id="withdrawAmountInput" placeholder="0.01" value="0.01" oninput="calculateWithdrawPreview()" required>
                                <span style="font-weight: 700; color: #f7931a;">BTC</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1.05rem; border-radius: var(--radius-md);">
                            Request Withdrawal
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History matching wallet page.png -->
    <div class="widget-card">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 1.3rem; font-weight: 800;">Transaction History</h3>
            
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn btn-secondary btn-sm filter-tx-btn active" onclick="filterTransactions('All', this)" style="background: var(--bg-card); border-color: var(--accent-green); color: var(--accent-green);">All</button>
                <button type="button" class="btn btn-secondary btn-sm filter-tx-btn" onclick="filterTransactions('Swap', this)">Swap</button>
                <button type="button" class="btn btn-secondary btn-sm filter-tx-btn" onclick="filterTransactions('Deposit', this)">Deposit</button>
                <button type="button" class="btn btn-secondary btn-sm filter-tx-btn" onclick="filterTransactions('Withdrawal', this)">Withdrawal</button>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="trades-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Details</th>
                        <th>Date</th>
                        <th style="text-align: right;">Status</th>
                    </tr>
                </thead>
                <tbody id="transactionsTbody">
                    @forelse($transactions as $tx)
                        <tr class="tx-row" data-type="{{ $tx['type'] }}">
                            <td style="font-weight: 700;">{{ $tx['type'] }}</td>
                            <td style="font-family: var(--font-mono);">{{ $tx['details'] }}</td>
                            <td style="color: var(--text-muted); font-size: 0.85rem;">{{ $tx['date'] }}</td>
                            <td style="text-align: right;">
                                <span class="badge {{ $tx['status_class'] }}">
                                    {{ $tx['status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 24px;">No transactions recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    let btcPrice = {{ $btcPriceUsd }};
    let swapFeePct = {{ $swapFeePercent }};
    let netFeeBtc = {{ $withdrawalNetworkFee }};
    let userBtcBal = {{ $user->btc_balance }};
    let qrcodeInstance = null;

    function setRightPanelTab(tab) {
        const pDep = document.getElementById('panelDeposit');
        const pWith = document.getElementById('panelWithdraw');
        const tDep = document.getElementById('tabNavDeposit');
        const tWith = document.getElementById('tabNavWithdraw');

        if (tab === 'deposit') {
            pDep.style.display = 'block';
            pWith.style.display = 'none';
            tDep.classList.add('active');
            tWith.classList.remove('active');
        } else {
            pDep.style.display = 'none';
            pWith.style.display = 'block';
            tWith.classList.add('active');
            tDep.classList.remove('active');
        }
    }

    // QR Code generation
    function updateQrCode(address) {
        const container = document.getElementById('depositQrContainer');
        container.innerHTML = '';
        new QRCode(container, {
            text: address,
            width: 140,
            height: 140,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    }

    function handleDepositMethodChange() {
        const select = document.getElementById('depositMethodSelect');
        const opt = select.options[select.selectedIndex];
        const addr = opt.getAttribute('data-address');
        const cur = opt.getAttribute('data-currency');
        const notes = opt.getAttribute('data-notes');

        document.getElementById('depositAddressField').value = addr;
        document.getElementById('hiddenDepositMethodId').value = select.value;
        document.getElementById('depositCurrencyLabel').innerText = cur;
        document.getElementById('depositInstructions').innerText = notes;
        updateQrCode(addr);
    }

    function copyDepositAddress() {
        const addr = document.getElementById('depositAddressField').value;
        navigator.clipboard.writeText(addr);
        alert('Deposit address copied: ' + addr);
    }

    // Swap Calculations
    function handleSwapCoinChange() {
        const select = document.getElementById('swapCoinSelect');
        const opt = select.options[select.selectedIndex];
        const bal = opt.getAttribute('data-balance') || 0;
        document.getElementById('swapFromBalance').innerText = Number(bal).toLocaleString();
        calculateSwapPreview();
    }

    async function calculateSwapPreview() {
        const coinId = document.getElementById('swapCoinSelect').value;
        const amount = parseFloat(document.getElementById('swapTokenAmount').value) || 0;

        if (amount <= 0) {
            document.getElementById('swapBtcOutput').value = '';
            return;
        }

        try {
            const res = await fetch(`/api/swap/quote?coin_id=${coinId}&amount=${amount}`);
            const data = await res.json();

            document.getElementById('swapBtcOutput').value = data.btc_gross;
            document.getElementById('swapRateDisplay').innerText = data.rate_formatted;
            document.getElementById('swapFeeDisplay').innerText = data.fee_btc + ' BTC';
            document.getElementById('swapNetDisplay').innerText = data.net_btc + ' BTC';
        } catch (err) {
            console.error(err);
        }
    }

    // Withdrawal Preview
    function calculateWithdrawPreview() {
        const amount = parseFloat(document.getElementById('withdrawAmountInput').value) || 0;
        const net = Math.max(0, amount - netFeeBtc);
        document.getElementById('withdrawNetDisplay').innerText = net.toFixed(6) + ' BTC';
    }

    function setMaxWithdrawal() {
        document.getElementById('withdrawAmountInput').value = userBtcBal.toFixed(6);
        calculateWithdrawPreview();
    }

    function validateWithdrawal(e) {
        const amount = parseFloat(document.getElementById('withdrawAmountInput').value) || 0;
        if (amount <= netFeeBtc) {
            alert(`Amount must exceed network fee of ${netFeeBtc} BTC.`);
            return false;
        }
        if (amount > userBtcBal) {
            alert(`Insufficient BTC balance. You have ${userBtcBal} BTC.`);
            return false;
        }
        return true;
    }

    // Transaction filter
    function filterTransactions(type, btn) {
        document.querySelectorAll('.filter-tx-btn').forEach(b => {
            b.style.background = 'transparent';
            b.style.borderColor = 'var(--border-color)';
            b.style.color = 'var(--text-secondary)';
        });
        btn.style.background = 'var(--bg-card)';
        btn.style.borderColor = 'var(--accent-green)';
        btn.style.color = 'var(--accent-green)';

        document.querySelectorAll('.tx-row').forEach(row => {
            if (type === 'All' || row.getAttribute('data-type') === type) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        handleDepositMethodChange();
        handleSwapCoinChange();
        calculateWithdrawPreview();

        @if(request('tab') === 'withdraw')
            setRightPanelTab('withdraw');
        @elseif(request('tab') === 'deposit')
            setRightPanelTab('deposit');
        @endif
    });
</script>
@endpush
@endsection
