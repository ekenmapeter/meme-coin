<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\CoinTrade;
use App\Services\AuditLogger;
use App\Services\MarketSimulatorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminCoinController extends Controller
{
    protected const ALLOWED_LOGOS = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

    protected MarketSimulatorService $simulator;

    public function __construct(MarketSimulatorService $simulator)
    {
        $this->simulator = $simulator;
    }

    public function index()
    {
        $coins = Coin::orderBy('is_featured', 'desc')
            ->orderBy('market_cap', 'desc')
            ->paginate(15);

        return view('admin.coins.index', compact('coins'));
    }

    public function create()
    {
        return view('admin.coins.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:60|regex:/^[A-Za-z0-9 .\-_]+$/',
            'ticker' => 'required|string|max:12|alpha_dash|unique:coins,ticker',
            'coingecko_id' => 'nullable|string|max:60|regex:/^[a-z0-9\-]+$/',
            'description' => 'nullable|string|max:2000',
            'current_price' => 'required|numeric|gt:0',
            'total_supply' => 'required|numeric|gt:0',
            'holders_count' => 'required|integer|min:0',
            'buyers_count' => 'required|integer|min:0',
            'volume_24h' => 'required|numeric|min:0',
            'liquidity' => 'nullable|numeric|min:0',
            'logo' => 'nullable|image|mimes:'.implode(',', self::ALLOWED_LOGOS).'|max:2048',
            'price_movement_mode' => 'required|in:manual,auto_up,auto_down,auto_volatile',
            'auto_step_percent' => 'required|numeric|min:0.1|max:50',
            'auto_interval_seconds' => 'required|integer|min:3|max:3600',
        ]);

        $price = (float) $validated['current_price'];
        $totalSupply = (float) $validated['total_supply'];

        DB::transaction(function () use ($request, $price, $totalSupply) {
            $coin = Coin::create([
                'name' => strtoupper($request->name),
                'ticker' => strtoupper($request->ticker),
                'coingecko_id' => strtolower(trim((string) $request->coingecko_id)) ?: null,
                'description' => $request->description,
                'logo_path' => $this->storeLogo($request),
                'contract_address' => '8x'.Str::random(32),
                'network' => $request->input('network', 'Solana'),
                'current_price' => $price,
                'initial_price' => $price,
                'market_cap' => $price * $totalSupply,
                'change_24h' => 0.0,
                'volume_24h' => (float) $request->volume_24h,
                'holders_count' => (int) $request->holders_count,
                'buyers_count' => (int) $request->buyers_count,
                'liquidity' => (float) ($request->liquidity ?: 50000),
                'total_supply' => $totalSupply,
                'is_featured' => $request->boolean('is_featured'),
                'is_trending' => $request->boolean('is_trending'),
                'is_active' => true,
                'price_movement_mode' => $request->price_movement_mode,
                'auto_step_percent' => (float) $request->auto_step_percent,
                'auto_interval_seconds' => (int) $request->auto_interval_seconds,
            ]);

            $this->simulator->recordCandle($coin, $price * 0.95, $price, $coin->volume_24h, Carbon::now());

            AuditLogger::record('coin.created', $coin, ['ticker' => $coin->ticker]);
        });

        return redirect()->route('admin.coins.index')->with('success', "Coin {$request->name} created successfully!");
    }

    public function edit(Coin $coin)
    {
        $recentTrades = CoinTrade::where('coin_id', $coin->id)->latest()->take(10)->get();

        return view('admin.coins.edit', compact('coin', 'recentTrades'));
    }

    public function update(Request $request, Coin $coin)
    {
        $request->validate([
            'name' => 'required|string|max:60|regex:/^[A-Za-z0-9 .\-_]+$/',
            'ticker' => 'required|string|max:12|alpha_dash|unique:coins,ticker,'.$coin->id,
            'coingecko_id' => 'nullable|string|max:60|regex:/^[a-z0-9\-]+$/',
            'description' => 'nullable|string|max:2000',
            'current_price' => 'required|numeric|gt:0',
            'total_supply' => 'required|numeric|gt:0',
            'holders_count' => 'required|integer|min:0',
            'buyers_count' => 'required|integer|min:0',
            'volume_24h' => 'required|numeric|min:0',
            'liquidity' => 'nullable|numeric|min:0',
            'change_24h' => 'nullable|numeric',
            'price_movement_mode' => 'required|in:manual,auto_up,auto_down,auto_volatile',
            'auto_step_percent' => 'required|numeric|min:0.1|max:50',
            'auto_interval_seconds' => 'required|integer|min:3|max:3600',
            'logo' => 'nullable|image|mimes:'.implode(',', self::ALLOWED_LOGOS).'|max:2048',
        ]);

        $oldPrice = (float) $coin->current_price;
        $newPrice = (float) $request->current_price;
        $totalSupply = (float) $request->total_supply;

        DB::transaction(function () use ($coin, $request, $oldPrice, $newPrice, $totalSupply) {
            if ($request->hasFile('logo')) {
                $coin->logo_path = $this->storeLogo($request);
            }

            $coin->name = strtoupper($request->name);
            $coin->ticker = strtoupper($request->ticker);
            $coin->coingecko_id = strtolower(trim((string) $request->coingecko_id)) ?: null;
            $coin->description = $request->description;
            $coin->current_price = $newPrice;
            $coin->market_cap = $newPrice * $totalSupply;
            $coin->total_supply = $totalSupply;
            $coin->holders_count = (int) $request->holders_count;
            $coin->buyers_count = (int) $request->buyers_count;
            $coin->volume_24h = (float) $request->volume_24h;
            $coin->liquidity = (float) ($request->liquidity ?: $coin->liquidity);
            $coin->change_24h = (float) ($request->change_24h ?? $coin->change_24h);
            $coin->price_movement_mode = $request->price_movement_mode;
            $coin->auto_step_percent = (float) $request->auto_step_percent;
            $coin->auto_interval_seconds = (int) $request->auto_interval_seconds;
            $coin->is_featured = $request->boolean('is_featured');
            $coin->is_trending = $request->boolean('is_trending');
            $coin->is_active = $request->boolean('is_active');
            $coin->save();

            if ($oldPrice != $newPrice) {
                $this->simulator->recordCandle($coin, $oldPrice, $newPrice, 500, Carbon::now());
            }

            AuditLogger::record('coin.updated', $coin, [
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
            ]);
        });

        return redirect()->route('admin.coins.edit', $coin->id)->with('success', "Coin {$coin->name} updated successfully!");
    }

    /**
     * Quick price update endpoint (ajax or form).
     */
    public function quickSetPrice(Request $request, Coin $coin)
    {
        $request->validate(['price' => 'required|numeric|gt:0']);
        $newPrice = (float) $request->price;

        DB::transaction(function () use ($coin, $newPrice) {
            $oldPrice = $coin->current_price;

            $coin->current_price = $newPrice;
            $coin->market_cap = $newPrice * ($coin->total_supply ?: 10000000000);
            if ($coin->initial_price > 0) {
                $coin->change_24h = (($newPrice - $coin->initial_price) / $coin->initial_price) * 100;
            }
            $coin->save();

            $this->simulator->recordCandle($coin, $oldPrice, $newPrice, 1000, Carbon::now());

            AuditLogger::record('coin.price.updated', $coin, [
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'new_price' => $newPrice,
                'formatted_price' => $coin->formatted_price,
                'market_cap' => $coin->formatted_market_cap,
            ]);
        }

        return back()->with('success', "Price for {$coin->ticker} updated to \${$newPrice}");
    }

    /**
     * Instantly simulate a Buy or Sell order.
     */
    public function simulateTrade(Request $request, Coin $coin)
    {
        $request->validate([
            'type' => 'required|in:buy,sell',
            'usd_amount' => 'required|numeric|min:1|max:50000',
        ]);

        $type = $request->input('type');
        $usdAmount = (float) $request->input('usd_amount');

        $trade = DB::transaction(function () use ($coin, $type, $usdAmount) {
            return $this->simulator->simulateTrade($coin, $type, $usdAmount);
        });

        AuditLogger::record('coin.trade.simulated', $coin, [
            'type' => $type,
            'usd_amount' => $usdAmount,
            'new_price' => $coin->current_price,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'trade' => $trade,
                'new_price' => $coin->current_price,
                'formatted_price' => $coin->formatted_price,
            ]);
        }

        return back()->with('success', "Simulated {$type} trade of \${$usdAmount} executed! Price updated to {$coin->formatted_price}.");
    }

    public function togglePause(Coin $coin)
    {
        $coin->is_active = ! $coin->is_active;
        $coin->save();

        $status = $coin->is_active ? 'resumed' : 'paused';

        return back()->with('success', "Coin {$coin->ticker} trading {$status}.");
    }

    public function toggleFeature(Coin $coin)
    {
        $coin->is_featured = ! $coin->is_featured;
        $coin->save();

        $status = $coin->is_featured ? 'featured on homepage' : 'removed from featured';

        return back()->with('success', "Coin {$coin->ticker} {$status}.");
    }

    /**
     * Deactivates a coin: users keep their holdings, but new trades are blocked.
     * Hard deletion was removed because it cascade-wiped user holdings/swaps.
     */
    public function deactivate(Coin $coin)
    {
        $coin->is_active = false;
        $coin->save();

        AuditLogger::record('coin.deactivated', $coin);

        return back()->with('success', "Coin {$coin->ticker} deactivated. New trades are blocked; holdings preserved.");
    }

    protected function storeLogo(Request $request): string
    {
        if (! $request->hasFile('logo')) {
            return 'pepeking.png';
        }

        $file = $request->file('logo');
        $extension = $file->guessExtension();

        if (! in_array($extension, self::ALLOWED_LOGOS, true)) {
            $extension = 'png';
        }

        $fileName = time().'_'.Str::slug((string) $request->ticker).'.'.$extension;
        $file->move(public_path('images/coins'), $fileName);

        return $fileName;
    }
}
