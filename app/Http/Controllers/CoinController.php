<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Models\CoinTrade;
use App\Models\PlatformSetting;
use App\Models\PriceHistory;
use App\Models\UserHolding;
use App\Services\MarketSimulatorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CoinController extends Controller
{
    protected MarketSimulatorService $simulator;

    public function __construct(MarketSimulatorService $simulator)
    {
        $this->simulator = $simulator;
    }

    public function index()
    {
        $coins = Coin::where('is_active', true)
            ->orderBy('market_cap', 'desc')
            ->paginate(15);

        return view('coins.index', compact('coins'));
    }

    public function show(string $ticker)
    {
        $coin = Coin::where('ticker', strtoupper($ticker))
            ->orWhere('ticker', $ticker)
            ->firstOrFail();

        // Tick auto coins if needed
        $this->simulator->tickCoin($coin);

        $recentTrades = CoinTrade::where('coin_id', $coin->id)
            ->latest()
            ->take(10)
            ->get();

        $userHolding = null;
        if (Auth::check()) {
            $userHolding = UserHolding::where('user_id', Auth::id())
                ->where('coin_id', $coin->id)
                ->first();
        }

        $solPrice = (float) PlatformSetting::get('sol_usd_price', 142.50);

        return view('coins.show', compact('coin', 'recentTrades', 'userHolding', 'solPrice'));
    }

    public function chartData(string $ticker, Request $request)
    {
        $coin = Coin::where('ticker', strtoupper($ticker))->firstOrFail();
        $timeframe = $request->input('timeframe', '1D');

        // Fetch or auto-generate points for the timeframe
        $candles = PriceHistory::where('coin_id', $coin->id)
            ->where('timeframe', $timeframe)
            ->orderBy('candle_time', 'asc')
            ->get();

        if ($candles->isEmpty()) {
            // Fallback to 1D or generate synthetic points for selected timeframe
            $candles = PriceHistory::where('coin_id', $coin->id)
                ->where('timeframe', '1D')
                ->orderBy('candle_time', 'asc')
                ->get();
        }

        $formatted = $candles->map(function ($c) {
            return [
                'time' => $c->candle_time->timestamp,
                'open' => (float) $c->open,
                'high' => (float) $c->high,
                'low' => (float) $c->low,
                'close' => (float) $c->close,
                'volume' => (float) $c->volume,
            ];
        });

        return response()->json([
            'ticker' => $coin->ticker,
            'current_price' => $coin->current_price,
            'change_24h' => $coin->change_24h,
            'candles' => $formatted,
        ]);
    }

    public function trades(string $ticker)
    {
        $coin = Coin::where('ticker', strtoupper($ticker))->firstOrFail();
        $this->simulator->tickCoin($coin);

        $trades = CoinTrade::where('coin_id', $coin->id)
            ->latest()
            ->take(12)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'wallet' => $t->short_wallet,
                    'type' => $t->type,
                    'usd_amount' => number_format($t->usd_amount, 2),
                    'token_amount' => Coin::formatNumberAbbreviated($t->token_amount, false),
                    'time_ago' => $t->created_at->diffForHumans(null, true, true),
                ];
            });

        // Buy / sell pressure from the last 100 trades
        $recent = CoinTrade::where('coin_id', $coin->id)
            ->latest()
            ->take(100)
            ->get(['type', 'usd_amount']);

        $buyCount = $recent->where('type', 'buy')->count();
        $sellCount = $recent->where('type', 'sell')->count();
        $buyVolume = (float) $recent->where('type', 'buy')->sum('usd_amount');
        $sellVolume = (float) $recent->where('type', 'sell')->sum('usd_amount');
        $totalVolume = $buyVolume + $sellVolume;
        $buyPressure = $totalVolume > 0 ? round(($buyVolume / $totalVolume) * 100, 1) : 50;

        // 24h high / low from today's candles
        $todayCandle = PriceHistory::where('coin_id', $coin->id)
            ->where('timeframe', '1D')
            ->whereDate('candle_time', now()->toDateString())
            ->orderBy('candle_time', 'desc')
            ->first();

        $high24h = $todayCandle ? (float) $todayCandle->high : (float) $coin->current_price;
        $low24h = $todayCandle ? (float) $todayCandle->low : (float) $coin->current_price;

        return response()->json([
            'current_price' => $coin->current_price,
            'formatted_price' => $coin->formatted_price,
            'change_24h' => $coin->change_24h,
            'market_cap' => $coin->formatted_market_cap,
            'volume_24h' => $coin->formatted_volume,
            'holders' => $coin->formatted_holders,
            'buyers' => number_format($coin->buyers_count),
            'liquidity' => $coin->formatted_liquidity,
            'total_supply' => $coin->formatted_total_supply,
            'high_24h' => Coin::formatPriceShort((float) $high24h),
            'low_24h' => Coin::formatPriceShort((float) $low24h),
            'buy_count' => $buyCount,
            'sell_count' => $sellCount,
            'buy_pressure' => $buyPressure,
            'sell_volume' => number_format($sellVolume, 0),
            'buy_volume' => number_format($buyVolume, 0),
            'trades' => $trades,
        ]);
    }

    public function trade(Request $request, string $ticker)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Please connect your wallet or log in to trade.'], 401);
        }

        $request->validate([
            'type' => 'required|in:buy,sell',
            'amount' => 'required|numeric|gt:0',
            'currency' => 'nullable|in:SOL,USD',
        ]);

        $coin = Coin::where('ticker', strtoupper($ticker))->firstOrFail();
        $type = $request->input('type');
        $amount = (float) $request->input('amount');
        $currency = $request->input('currency', 'SOL');

        try {
            $result = $this->simulator->executeUserTrade(Auth::user(), $coin, $type, $amount, $currency);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function launch()
    {
        return view('coins.launch');
    }

    public function storeLaunch(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|regex:/^[A-Za-z0-9 .\-_]+$/',
            'ticker' => 'required|string|max:10|alpha_dash|unique:coins,ticker',
            'description' => 'required|string|max:1000',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:2048',
            'initial_liquidity' => 'nullable|numeric|min:0.1',
        ]);

        $logoPath = 'pepeking.png';
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $extension = $file->guessExtension();
            if (! in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
                $extension = 'png';
            }
            $fileName = time().'_'.Str::slug($request->ticker).'.'.$extension;
            $file->move(public_path('images/coins'), $fileName);
            $logoPath = $fileName;
        }

        $contract = '8x'.Str::random(32);
        $initialPrice = 0.000010;
        $totalSupply = 1000000000;

        $coin = DB::transaction(function () use ($request, $logoPath, $contract, $initialPrice, $totalSupply) {
            $coin = Coin::create([
                'name' => strtoupper($request->name),
                'ticker' => strtoupper($request->ticker),
                'description' => $request->description,
                'logo_path' => $logoPath,
                'contract_address' => $contract,
                'network' => 'Solana',
                'current_price' => $initialPrice,
                'initial_price' => $initialPrice,
                'market_cap' => $initialPrice * $totalSupply,
                'change_24h' => 0.0,
                'volume_24h' => 500.0,
                'holders_count' => 1,
                'buyers_count' => 1,
                'liquidity' => 5000.0,
                'total_supply' => $totalSupply,
                'is_featured' => false,
                'is_trending' => false,
                'is_active' => true,
                'price_movement_mode' => 'auto_up',
                'auto_step_percent' => 2.0,
                'auto_interval_seconds' => 10,
            ]);

            $this->simulator->recordCandle($coin, $initialPrice, $initialPrice, 500, Carbon::now());

            // If user logged in, grant creator tokens
            if (Auth::check()) {
                UserHolding::create([
                    'user_id' => Auth::id(),
                    'coin_id' => $coin->id,
                    'token_balance' => 50000000,
                    'avg_buy_price' => $initialPrice,
                ]);
            }

            return $coin;
        });

        return redirect()->route('coins.show', $coin->ticker)->with('success', "{$coin->name} has been launched successfully on Pump Endless!");
    }
}
