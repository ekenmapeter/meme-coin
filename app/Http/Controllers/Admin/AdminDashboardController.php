<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\CoinTrade;
use App\Models\Deposit;
use App\Models\Swap;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\CryptoPriceService;

class AdminDashboardController extends Controller
{
    protected CryptoPriceService $prices;

    public function __construct(CryptoPriceService $prices)
    {
        $this->prices = $prices;
    }

    public function index()
    {
        $totalUsers = User::count();
        $totalCoins = Coin::count();
        $totalVolume = Coin::sum('volume_24h');
        $btcUsd = $this->prices->btcUsd();

        // Fees collected (from swaps + completed withdrawals), valued in USD.
        $swapFeesUsd = Swap::sum('fee_btc') * $btcUsd;
        $withdrawalFeesUsd = Withdrawal::where('status', 'completed')->sum('network_fee') * $btcUsd;
        $totalFees = $swapFeesUsd + $withdrawalFeesUsd;

        $pendingDeposits = Deposit::where('status', 'pending')->count();
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();

        $recentCoins = Coin::orderBy('updated_at', 'desc')->take(6)->get();

        $recentDeposits = Deposit::with('user')->latest()->take(5)->get();
        $recentWithdrawals = Withdrawal::with('user')->latest()->take(5)->get();

        // Top traders by simulated volume, derived from live trade data.
        $topTraders = CoinTrade::query()
            ->whereNotNull('user_id')
            ->with('user')
            ->selectRaw('user_id, SUM(usd_amount) as total_volume')
            ->groupBy('user_id')
            ->orderByDesc('total_volume')
            ->take(5)
            ->get()
            ->map(function ($row) {
                return [
                    'wallet' => $row->user
                        ? substr($row->user->wallet_address ?? $row->user->email, 0, 6).'...'.substr($row->user->wallet_address ?? $row->user->email, -4)
                        : 'Anonymous',
                    'volume' => '$'.number_format((float) $row->total_volume),
                ];
            })
            ->all();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalCoins',
            'totalVolume',
            'totalFees',
            'pendingDeposits',
            'pendingWithdrawals',
            'recentCoins',
            'recentDeposits',
            'recentWithdrawals',
            'topTraders'
        ));
    }
}
