<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Models\PlatformSetting;
use App\Models\Swap;
use App\Models\UserHolding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $btcPriceUsd = (float) PlatformSetting::get('btc_usd_price', 66450.00);
        $solPriceUsd = (float) PlatformSetting::get('sol_usd_price', 142.50);

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
