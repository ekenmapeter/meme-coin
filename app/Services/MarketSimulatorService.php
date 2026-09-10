<?php

namespace App\Services;

use App\Models\Coin;
use App\Models\CoinTrade;
use App\Models\PlatformSetting;
use App\Models\PriceHistory;
use App\Models\Swap;
use App\Models\User;
use App\Models\UserHolding;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MarketSimulatorService
{
    protected CryptoPriceService $prices;

    public function __construct(CryptoPriceService $prices)
    {
        $this->prices = $prices;
    }

    /**
     * Ticks a coin's price according to its mode if enough time has passed.
     */
    public function tickCoin(Coin $coin): bool
    {
        if (! $coin->is_active || $coin->price_movement_mode === 'manual' || $coin->coingecko_id) {
            return false;
        }

        $now = Carbon::now();
        if ($coin->last_auto_tick_at && $now->diffInSeconds($coin->last_auto_tick_at) < $coin->auto_interval_seconds) {
            return false;
        }

        $mode = $coin->price_movement_mode;
        $stepPercent = $coin->auto_step_percent / 100;

        $multiplier = 1.0;
        $isBuy = true;

        if ($mode === 'auto_up') {
            // 75% chance of up, 25% chance of small pullback
            if (rand(1, 100) <= 75) {
                $multiplier = 1 + (rand(10, 100) / 100) * $stepPercent;
                $isBuy = true;
            } else {
                $multiplier = 1 - (rand(10, 50) / 100) * ($stepPercent * 0.5);
                $isBuy = false;
            }
        } elseif ($mode === 'auto_down') {
            // 75% chance of down
            if (rand(1, 100) <= 75) {
                $multiplier = 1 - (rand(10, 100) / 100) * $stepPercent;
                $isBuy = false;
            } else {
                $multiplier = 1 + (rand(10, 50) / 100) * ($stepPercent * 0.5);
                $isBuy = true;
            }
        } else { // auto_volatile
            $rand = rand(1, 100);
            if ($rand <= 52) {
                $multiplier = 1 + (rand(10, 100) / 100) * $stepPercent;
                $isBuy = true;
            } else {
                $multiplier = 1 - (rand(10, 100) / 100) * $stepPercent;
                $isBuy = false;
            }
        }

        $newPrice = max(0.00000001, $coin->current_price * $multiplier);
        $oldPrice = $coin->current_price;
        $coin->current_price = $newPrice;
        $coin->market_cap = $newPrice * ($coin->total_supply ?: 10000000000);

        // Update 24h change relative to initial price
        if ($coin->initial_price > 0) {
            $coin->change_24h = (($newPrice - $coin->initial_price) / $coin->initial_price) * 100;
        }

        // Generate simulated trade
        $tradeUsd = rand(50, 950) + (rand(1, 99) / 100);
        $tokenAmount = $tradeUsd / $newPrice;
        $coin->volume_24h += $tradeUsd;
        $coin->buyers_count += rand(0, 2);
        if ($isBuy && rand(1, 5) === 1) {
            $coin->holders_count += 1;
        }

        $coin->last_auto_tick_at = $now;
        $coin->save();

        // Record simulated trade
        $simulatedWallet = '0x'.substr(md5(uniqid(mt_rand(), true)), 0, 16);
        CoinTrade::create([
            'coin_id' => $coin->id,
            'user_id' => null,
            'wallet_address' => $simulatedWallet,
            'type' => $isBuy ? 'buy' : 'sell',
            'token_amount' => $tokenAmount,
            'usd_amount' => $tradeUsd,
            'price' => $newPrice,
            'created_at' => $now,
        ]);

        // Update 1D / 1h candle
        $this->recordCandle($coin, $oldPrice, $newPrice, $tradeUsd, $now);

        return true;
    }

    /**
     * Updates the latest candle or creates a new one for chart display.
     */
    public function recordCandle(Coin $coin, float $oldPrice, float $newPrice, float $volume, Carbon $now): void
    {
        $latestCandle = PriceHistory::where('coin_id', $coin->id)
            ->where('timeframe', '1D')
            ->orderBy('candle_time', 'desc')
            ->first();

        if ($latestCandle && $latestCandle->candle_time->isToday()) {
            $latestCandle->close = $newPrice;
            $latestCandle->high = max($latestCandle->high, $newPrice);
            $latestCandle->low = min($latestCandle->low, $newPrice);
            $latestCandle->volume += $volume;
            $latestCandle->save();
        } else {
            PriceHistory::create([
                'coin_id' => $coin->id,
                'timeframe' => '1D',
                'open' => $oldPrice,
                'high' => max($oldPrice, $newPrice),
                'low' => min($oldPrice, $newPrice),
                'close' => $newPrice,
                'volume' => $volume,
                'candle_time' => $now,
            ]);
        }
    }

    /**
     * Executes manual simulated trade from admin.
     */
    public function simulateTrade(Coin $coin, string $type = 'buy', ?float $usdAmount = null): CoinTrade
    {
        $usdAmount = $usdAmount ?? rand(100, 1500);
        $impactPercent = min(5.0, ($usdAmount / 10000) * 1.5);
        $multiplier = $type === 'buy' ? (1 + ($impactPercent / 100)) : (1 - ($impactPercent / 100));

        $oldPrice = $coin->current_price;
        $newPrice = max(0.00000001, $coin->current_price * $multiplier);

        $coin->current_price = $newPrice;
        $coin->market_cap = $newPrice * ($coin->total_supply ?: 10000000000);
        if ($coin->initial_price > 0) {
            $coin->change_24h = (($newPrice - $coin->initial_price) / $coin->initial_price) * 100;
        }
        $coin->volume_24h += $usdAmount;
        $coin->buyers_count += 1;
        if ($type === 'buy') {
            $coin->holders_count += rand(0, 1);
        }
        $coin->save();

        $tokenAmount = $usdAmount / $newPrice;
        $wallet = '0x'.substr(md5(uniqid(mt_rand(), true)), 0, 16);

        $trade = CoinTrade::create([
            'coin_id' => $coin->id,
            'user_id' => null,
            'wallet_address' => $wallet,
            'type' => $type,
            'token_amount' => $tokenAmount,
            'usd_amount' => $usdAmount,
            'price' => $newPrice,
        ]);

        $this->recordCandle($coin, $oldPrice, $newPrice, $usdAmount, Carbon::now());

        return $trade;
    }

    /**
     * User executes a Buy or Sell.
     */
    public function executeUserTrade(User $user, Coin $coin, string $type, float $amount, string $currency = 'SOL'): array
    {
        return DB::transaction(function () use ($user, $coin, $type, $amount, $currency) {
            if ($user->isRestricted()) {
                throw new \Exception('Your account is restricted from trading. Please contact support.');
            }

            if (! $coin->is_active) {
                throw new \Exception('Trading for this coin is currently paused.');
            }

            $solPrice = $this->prices->solUsd();

            if ($type === 'buy') {
                // $amount is how much SOL the user is paying
                $usdValue = ($currency === 'SOL') ? ($amount * $solPrice) : $amount;

                if ($currency === 'SOL' && $user->sol_balance < $amount) {
                    throw new \Exception('Insufficient SOL balance. You have '.number_format($user->sol_balance, 4).' SOL.');
                }
                if ($currency === 'USD' && $user->usd_balance < $amount) {
                    throw new \Exception('Insufficient USD balance.');
                }

                // Deduct currency
                if ($currency === 'SOL') {
                    $user->sol_balance -= $amount;
                } else {
                    $user->usd_balance -= $amount;
                }
                $user->save();

                // Slight price impact up
                $impact = min(2.0, ($usdValue / 5000) * 0.5);
                $execPrice = $coin->current_price * (1 + ($impact / 100));
                $tokenAmount = $usdValue / $execPrice;

                // Update coin
                $oldPrice = $coin->current_price;
                $coin->current_price = $execPrice;
                $coin->market_cap = $execPrice * ($coin->total_supply ?: 10000000000);
                if ($coin->initial_price > 0) {
                    $coin->change_24h = (($execPrice - $coin->initial_price) / $coin->initial_price) * 100;
                }
                $coin->volume_24h += $usdValue;
                $coin->buyers_count += 1;
                $coin->save();

                // Update user holdings
                $holding = UserHolding::firstOrNew([
                    'user_id' => $user->id,
                    'coin_id' => $coin->id,
                ]);

                $totalCost = ($holding->token_balance * $holding->avg_buy_price) + $usdValue;
                $newTokenBalance = $holding->token_balance + $tokenAmount;
                $holding->avg_buy_price = $newTokenBalance > 0 ? ($totalCost / $newTokenBalance) : $execPrice;
                $holding->token_balance = $newTokenBalance;
                $holding->save();

                // Trade log
                $trade = CoinTrade::create([
                    'coin_id' => $coin->id,
                    'user_id' => $user->id,
                    'wallet_address' => $user->wallet_address ?: ('0x'.substr(md5($user->id), 0, 16)),
                    'type' => 'buy',
                    'token_amount' => $tokenAmount,
                    'usd_amount' => $usdValue,
                    'price' => $execPrice,
                ]);

                $this->recordCandle($coin, $oldPrice, $execPrice, $usdValue, Carbon::now());

                return [
                    'success' => true,
                    'type' => 'buy',
                    'token_amount' => $tokenAmount,
                    'usd_value' => $usdValue,
                    'price' => $execPrice,
                    'user_sol_balance' => $user->sol_balance,
                    'user_token_balance' => $holding->token_balance,
                ];
            } else {
                // $amount is how many tokens the user wants to sell
                $holding = UserHolding::where('user_id', $user->id)
                    ->where('coin_id', $coin->id)
                    ->first();

                if (! $holding || $holding->token_balance < $amount) {
                    $avail = $holding ? number_format($holding->token_balance) : 0;
                    throw new \Exception("Insufficient token balance. You have {$avail} {$coin->ticker}.");
                }

                $usdValue = $amount * $coin->current_price;
                $solProceeds = $usdValue / $solPrice;

                // Deduct tokens
                $holding->token_balance -= $amount;
                $holding->save();

                // Credit user SOL
                $user->sol_balance += $solProceeds;
                $user->save();

                // Slight price impact down
                $impact = min(2.0, ($usdValue / 5000) * 0.5);
                $execPrice = max(0.00000001, $coin->current_price * (1 - ($impact / 100)));

                $oldPrice = $coin->current_price;
                $coin->current_price = $execPrice;
                $coin->market_cap = $execPrice * ($coin->total_supply ?: 10000000000);
                if ($coin->initial_price > 0) {
                    $coin->change_24h = (($execPrice - $coin->initial_price) / $coin->initial_price) * 100;
                }
                $coin->volume_24h += $usdValue;
                $coin->save();

                $trade = CoinTrade::create([
                    'coin_id' => $coin->id,
                    'user_id' => $user->id,
                    'wallet_address' => $user->wallet_address ?: ('0x'.substr(md5($user->id), 0, 16)),
                    'type' => 'sell',
                    'token_amount' => $amount,
                    'usd_amount' => $usdValue,
                    'price' => $execPrice,
                ]);

                $this->recordCandle($coin, $oldPrice, $execPrice, $usdValue, Carbon::now());

                return [
                    'success' => true,
                    'type' => 'sell',
                    'token_amount' => $amount,
                    'proceeds_sol' => $solProceeds,
                    'usd_value' => $usdValue,
                    'price' => $execPrice,
                    'user_sol_balance' => $user->sol_balance,
                    'user_token_balance' => $holding->token_balance,
                ];
            }
        });
    }

    /**
     * Executes Swap from Meme Coin to Bitcoin (BTC).
     */
    public function executeSwap(User $user, Coin $coin, float $tokenAmount): Swap
    {
        return DB::transaction(function () use ($user, $coin, $tokenAmount) {
            if ($user->isRestricted()) {
                throw new \Exception('Your account is restricted from swapping. Please contact support.');
            }

            if (! $coin->is_active) {
                throw new \Exception('Swapping this coin is currently paused.');
            }

            $holding = UserHolding::where('user_id', $user->id)
                ->where('coin_id', $coin->id)
                ->first();

            if (! $holding || $holding->token_balance < $tokenAmount) {
                $avail = $holding ? number_format($holding->token_balance) : 0;
                throw new \Exception("Insufficient token balance. You hold {$avail} {$coin->ticker}.");
            }

            $btcusd = $this->prices->btcUsd();
            $feePercent = (float) PlatformSetting::get('swap_fee_percent', 1.00);

            $usdValue = $tokenAmount * $coin->current_price;
            $btcAmountGross = $usdValue / $btcusd;
            $feeBtc = $btcAmountGross * ($feePercent / 100);
            $netBtcReceived = $btcAmountGross - $feeBtc;
            $rate = $coin->current_price / $btcusd;

            // Deduct meme coin tokens
            $holding->token_balance -= $tokenAmount;
            $holding->save();

            // Credit BTC balance
            $user->btc_balance += $netBtcReceived;
            $user->save();

            // Record swap
            $swap = Swap::create([
                'user_id' => $user->id,
                'coin_id' => $coin->id,
                'token_amount' => $tokenAmount,
                'usd_value' => $usdValue,
                'btc_amount_gross' => $btcAmountGross,
                'fee_percent' => $feePercent,
                'fee_btc' => $feeBtc,
                'net_btc_received' => $netBtcReceived,
                'rate' => $rate,
                'status' => 'completed',
            ]);

            return $swap;
        });
    }

    /**
     * Ticks all active auto coins.
     */
    public function tickAllCoins(): int
    {
        $coins = Coin::where('is_active', true)
            ->where('price_movement_mode', '!=', 'manual')
            ->get();

        $ticked = 0;
        foreach ($coins as $coin) {
            if ($this->tickCoin($coin)) {
                $ticked++;
            }
        }

        return $ticked;
    }

    /**
     * Overwrites simulated data with live CoinGecko market data for every
     * active coin that has a coingecko_id configured.
     */
    public function syncLiveCoinPrices(): int
    {
        $coins = Coin::where('is_active', true)
            ->whereNotNull('coingecko_id')
            ->get();

        if ($coins->isEmpty()) {
            return 0;
        }

        $live = $this->prices->coinMarketData($coins->pluck('coingecko_id')->all());

        $synced = 0;
        foreach ($coins as $coin) {
            $data = $live[$coin->coingecko_id] ?? null;
            if (! $data) {
                continue;
            }

            $coin->current_price = $data['price'];
            if ($data['market_cap'] > 0) {
                $coin->market_cap = $data['market_cap'];
            }
            if ($data['volume_24h'] > 0) {
                $coin->volume_24h = $data['volume_24h'];
            }
            $coin->change_24h = $data['change_24h'];
            $coin->save();

            $synced++;
        }

        return $synced;
    }
}
