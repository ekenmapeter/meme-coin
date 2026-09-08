<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $trendingCoins = Coin::where('is_active', true)
            ->where('is_trending', true)
            ->orderBy('change_24h', 'desc')
            ->take(5)
            ->get();

        // If less than 5 trending, fill with top by volume
        if ($trendingCoins->count() < 5) {
            $trendingCoins = Coin::where('is_active', true)
                ->orderBy('volume_24h', 'desc')
                ->take(5)
                ->get();
        }

        $newLaunches = Coin::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $allCoins = Coin::where('is_active', true)
            ->orderBy('market_cap', 'desc')
            ->get();

        $totalMarketCap = Coin::where('is_active', true)->sum('market_cap');
        $total24hVolume = Coin::where('is_active', true)->sum('volume_24h');
        $totalCoins = Coin::where('is_active', true)->count();

        $announcement = PlatformSetting::get('platform_announcement');

        return view('home', compact(
            'trendingCoins',
            'newLaunches',
            'allCoins',
            'totalMarketCap',
            'total24hVolume',
            'totalCoins',
            'announcement'
        ));
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        if (strlen($query) < 1) {
            return response()->json([]);
        }

        $coins = Coin::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('ticker', 'like', "%{$query}%")
                    ->orWhere('contract_address', 'like', "%{$query}%");
            })
            ->take(10)
            ->get(['id', 'name', 'ticker', 'current_price', 'change_24h', 'logo_path', 'market_cap']);

        return response()->json($coins);
    }
}
