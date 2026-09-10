<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Models\Deposit;
use App\Models\DepositMethod;
use App\Models\PlatformSetting;
use App\Models\Swap;
use App\Models\UserHolding;
use App\Models\Withdrawal;
use App\Services\CryptoPriceService;
use App\Services\MarketSimulatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    protected MarketSimulatorService $simulator;

    protected CryptoPriceService $prices;

    public function __construct(MarketSimulatorService $simulator, CryptoPriceService $prices)
    {
        $this->simulator = $simulator;
        $this->prices = $prices;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $this->simulator->syncLiveCoinPrices();

        $btcPriceUsd = $this->prices->btcUsd();
        $swapFeePercent = (float) PlatformSetting::get('swap_fee_percent', 1.00);
        $withdrawalNetworkFee = (float) PlatformSetting::get('btc_withdrawal_fee', 0.000300);

        // User Holdings
        $holdings = UserHolding::with('coin')
            ->where('user_id', $user->id)
            ->where('token_balance', '>', 0)
            ->get();

        // Calculate total balance valuation in BTC
        // user->btc_balance + holdings in BTC + SOL in BTC
        $solPriceUsd = $this->prices->solUsd();
        $solValueBtc = ($user->sol_balance * $solPriceUsd) / $btcPriceUsd;

        $holdingsValueUsd = 0;
        foreach ($holdings as $h) {
            $holdingsValueUsd += ($h->token_balance * ($h->coin->current_price ?? 0));
        }
        $holdingsValueBtc = $holdingsValueUsd / $btcPriceUsd;

        $totalBalanceBtc = $user->btc_balance; // Main Bitcoin Balance as shown in screenshot
        $totalValuationUsd = $totalBalanceBtc * $btcPriceUsd;

        // Deposit Methods
        $depositMethods = DepositMethod::where('is_active', true)->get();
        $defaultDepositMethod = $depositMethods->where('currency', 'BTC')->first() ?? $depositMethods->first();

        // Transaction History
        $deposits = Deposit::where('user_id', $user->id)->latest()->take(20)->get();
        $withdrawals = Withdrawal::where('user_id', $user->id)->latest()->take(20)->get();
        $swaps = Swap::with('coin')->where('user_id', $user->id)->latest()->take(20)->get();

        // Combine into unified transaction timeline
        $transactions = collect();
        foreach ($swaps as $s) {
            $transactions->push([
                'id' => 'SW-'.$s->id,
                'type' => 'Swap',
                'details' => number_format($s->token_amount).' '.($s->coin->name ?? 'COIN').' → '.number_format($s->net_btc_received, 6).' BTC',
                'date' => $s->created_at->format('M d, Y h:i A'),
                'created_at' => $s->created_at,
                'status' => 'Completed',
                'status_class' => 'badge-success',
            ]);
        }
        foreach ($deposits as $d) {
            $transactions->push([
                'id' => 'DP-'.$d->id,
                'type' => 'Deposit',
                'details' => number_format($d->amount, 4).' '.$d->currency,
                'date' => $d->created_at->format('M d, Y h:i A'),
                'created_at' => $d->created_at,
                'status' => ucfirst($d->status),
                'status_class' => $d->status === 'confirmed' ? 'badge-success' : ($d->status === 'pending' ? 'badge-warning' : 'badge-danger'),
            ]);
        }
        foreach ($withdrawals as $w) {
            $transactions->push([
                'id' => 'WD-'.$w->id,
                'type' => 'Withdrawal',
                'details' => number_format($w->amount, 4).' '.$w->currency,
                'date' => $w->created_at->format('M d, Y h:i A'),
                'created_at' => $w->created_at,
                'status' => ucfirst($w->status),
                'status_class' => $w->status === 'completed' || $w->status === 'approved' ? 'badge-success' : ($w->status === 'pending' ? 'badge-warning' : 'badge-danger'),
            ]);
        }
        $transactions = $transactions->sortByDesc('created_at')->values();

        // Coins available for swap
        $allCoins = Coin::where('is_active', true)->orderBy('name')->get();

        return view('wallet.index', compact(
            'user',
            'btcPriceUsd',
            'swapFeePercent',
            'withdrawalNetworkFee',
            'holdings',
            'totalBalanceBtc',
            'totalValuationUsd',
            'depositMethods',
            'defaultDepositMethod',
            'transactions',
            'allCoins'
        ));
    }

    public function swapQuote(Request $request)
    {
        $coinId = $request->input('coin_id');
        $amount = (float) $request->input('amount', 0);

        $coin = Coin::find($coinId);
        if (! $coin) {
            return response()->json(['error' => 'Coin not found'], 404);
        }

        $btcUsd = $this->prices->btcUsd();
        $swapFeePercent = (float) PlatformSetting::get('swap_fee_percent', 1.00);

        $usdValue = $amount * $coin->current_price;
        $btcGross = $btcUsd > 0 ? ($usdValue / $btcUsd) : 0;
        $feeBtc = $btcGross * ($swapFeePercent / 100);
        $netBtc = max(0, $btcGross - $feeBtc);
        $rate = $btcUsd > 0 ? ($coin->current_price / $btcUsd) : 0;

        return response()->json([
            'usd_value' => number_format($usdValue, 2),
            'btc_gross' => sprintf('%.8f', $btcGross),
            'fee_btc' => sprintf('%.8f', $feeBtc),
            'fee_percent' => $swapFeePercent,
            'net_btc' => sprintf('%.8f', $netBtc),
            'rate' => sprintf('%.14f', $rate),
            'rate_formatted' => '1 '.$coin->ticker.' = '.sprintf('%.10f', $rate).' BTC',
        ]);
    }

    public function submitSwap(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return back()->with('error', 'Please connect wallet to swap.');
        }

        $request->validate([
            'coin_id' => 'required|exists:coins,id',
            'token_amount' => 'required|numeric|gt:0',
        ]);

        $coin = Coin::findOrFail($request->input('coin_id'));
        $amount = (float) $request->input('token_amount');

        try {
            $swap = $this->simulator->executeSwap($user, $coin, $amount);

            return back()->with('success', 'Swap successful! Swapped '.number_format($swap->token_amount)." {$coin->ticker} for ".sprintf('%.6f', $swap->net_btc_received).' BTC.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function submitDeposit(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return back()->with('error', 'Please log in to submit a deposit.');
        }

        $request->validate([
            'deposit_method_id' => 'required|exists:deposit_methods,id',
            'amount' => 'required|numeric|gt:0',
            'txid' => 'required|string|min:6|max:120',
        ]);

        $method = DepositMethod::findOrFail($request->input('deposit_method_id'));

        $deposit = Deposit::create([
            'user_id' => $user->id,
            'deposit_method_id' => $method->id,
            'currency' => $method->currency,
            'amount' => $request->input('amount'),
            'txid' => $request->input('txid'),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Deposit request submitted successfully! It is now pending admin confirmation.');
    }

    public function submitWithdrawal(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return back()->with('error', 'Please log in to request a withdrawal.');
        }

        $request->validate([
            'destination_address' => 'required|string|min:15|max:100',
            'amount' => 'required|numeric|gt:0',
        ]);

        $amount = (float) $request->input('amount');
        $fee = (float) PlatformSetting::get('btc_withdrawal_fee', 0.000300);

        if ($amount <= $fee) {
            return back()->with('error', 'Withdrawal amount must be greater than the network fee ('.sprintf('%.6f', $fee).' BTC).');
        }

        if ($user->btc_balance < $amount) {
            return back()->with('error', 'Insufficient Bitcoin balance. Your balance is '.sprintf('%.8f', $user->btc_balance).' BTC.');
        }

        // Deduct from balance
        $user->btc_balance -= $amount;
        $user->save();

        $netAmount = $amount - $fee;

        Withdrawal::create([
            'user_id' => $user->id,
            'currency' => 'BTC',
            'amount' => $amount,
            'network_fee' => $fee,
            'net_amount' => $netAmount,
            'destination_address' => $request->input('destination_address'),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Withdrawal request for '.sprintf('%.6f', $amount).' BTC submitted. Admin review pending.');
    }
}
