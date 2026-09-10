<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Models\Swap;
use App\Models\UserHolding;
use App\Services\CryptoPriceService;
use App\Services\MarketSimulatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    protected CryptoPriceService $prices;

    protected MarketSimulatorService $simulator;

    public function __construct(CryptoPriceService $prices, MarketSimulatorService $simulator)
    {
        $this->prices = $prices;
        $this->simulator = $simulator;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $this->simulator->syncLiveCoinPrices();

        $btcPriceUsd = $this->prices->btcUsd();
        $solPriceUsd = $this->prices->solUsd();

        $holdings = UserHolding::with('coin')
            ->where('user_id', $user->id)
            ->where('token_balance', '>', 0)
            ->get()
            ->map(function ($h) {
                return [
                    'id' => $h->id,
                    'coin' => $h->coin,
                    'token_balance' => $h->token_balance,
                    'avg_buy_price' => $h->avg_buy_price,
                    'current_value_usd' => $h->token_balance * ($h->coin->current_price ?? 0),
                ];
            })
            ->sortByDesc('current_value_usd')
            ->values();

        $portfolioUsd = $holdings->sum('current_value_usd') + $user->usd_balance + ($user->btc_balance * $btcPriceUsd) + ($user->sol_balance * $solPriceUsd);

        $recentSwaps = Swap::with('coin')->where('user_id', $user->id)->latest()->take(10)->get();
        $recentDeposits = $user->deposits()->latest()->take(10)->get();
        $recentWithdrawals = $user->withdrawals()->latest()->take(10)->get();

        $topCoins = Coin::where('is_active', true)->orderBy('market_cap', 'desc')->take(5)->get();

        return view('dashboard', compact(
            'user',
            'btcPriceUsd',
            'solPriceUsd',
            'holdings',
            'portfolioUsd',
            'recentSwaps',
            'recentDeposits',
            'recentWithdrawals',
            'topCoins'
        ));
    }
}
