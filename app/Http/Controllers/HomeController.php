<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Models\PlatformSetting;
use App\Services\CryptoPriceService;
use App\Services\MarketSimulatorService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected MarketSimulatorService $simulator;

    protected CryptoPriceService $prices;

    public function __construct(MarketSimulatorService $simulator, CryptoPriceService $prices)
    {
        $this->simulator = $simulator;
        $this->prices = $prices;
    }

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
            ->take(5)
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
            ->get(['id', 'name', 'ticker', 'current_price', 'change_24h', 'logo_path', 'market_cap'])
            ->map(function (Coin $coin) {
                return [
                    'id' => $coin->id,
                    'name' => $coin->name,
                    'ticker' => $coin->ticker,
                    'current_price' => $coin->current_price,
                    'change_24h' => $coin->change_24h,
                    'logo_url' => $coin->logo_url,
                    'market_cap' => $coin->market_cap,
                ];
            });

        return response()->json($coins);
    }

    /**
     * Live market data for the frontpage: ticks due coins, then returns
     * everything the UI needs to update prices/stats without a reload.
     */
    public function liveMarket()
    {
        $this->simulator->tickAllCoins();
        $this->simulator->syncLiveCoinPrices();

        $coins = Coin::where('is_active', true)
            ->orderBy('market_cap', 'desc')
            ->get()
            ->map(function (Coin $coin) {
                return [
                    'ticker' => $coin->ticker,
                    'name' => $coin->name,
                    'logo_url' => $coin->logo_url,
                    'price' => (float) $coin->current_price,
                    'formatted_price' => $coin->formatted_price,
                    'change_24h' => (float) $coin->change_24h,
                    'market_cap' => (float) $coin->market_cap,
                    'formatted_market_cap' => $coin->formatted_market_cap,
                    'volume_24h' => (float) $coin->volume_24h,
                    'formatted_volume' => $coin->formatted_volume,
                    'holders_count' => (int) $coin->holders_count,
                    'formatted_holders' => $coin->formatted_holders,
                ];
            });

        return response()->json([
            'coins' => $coins,
            'sol_usd_price' => $this->prices->solUsd(),
            'total_market_cap' => (float) $coins->sum('market_cap'),
            'total_volume_24h' => (float) $coins->sum('volume_24h'),
            'total_coins' => $coins->count(),
            'updated_at' => now()->timestamp,
        ]);
    }
}
