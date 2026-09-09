<?php

namespace Database\Seeders;

use App\Models\Coin;
use App\Models\CoinTrade;
use App\Models\DepositMethod;
use App\Models\PlatformSetting;
use App\Models\PriceHistory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Platform Settings
        PlatformSetting::set('swap_fee_percent', '1.00');
        PlatformSetting::set('btc_withdrawal_fee', '0.000300');
        PlatformSetting::set('btc_usd_price', '66450.00');
        PlatformSetting::set('sol_usd_price', '142.50');
        PlatformSetting::set('platform_name', 'Pump Endless');
        PlatformSetting::set('platform_announcement', 'Welcome to Pump Endless! Trade simulated meme coins at lightning speed.');
        PlatformSetting::set('site_name', 'Pump Endless');
        PlatformSetting::set('site_description', 'Pump Endless – a high-speed demo meme-coin launch & trading platform. All prices and activity are simulated.');

        // 2. Deposit Methods (BTC & SOL only — each currency is credited to its matching balance)
        $btcMethod = DepositMethod::create([
            'currency' => 'BTC',
            'name' => 'Bitcoin (BTC)',
            'network' => 'Bitcoin Network',
            'wallet_address' => 'bc1qxy2kgdygjrsztqz2n0yrf2493p831kkfjhx0wlh',
            'min_deposit' => '0.001 BTC',
            'confirmations_required' => 3,
            'notes' => 'Send only BTC to this address. Minimum deposit: 0.001 BTC. Deposits will be credited after 3 confirmations.',
            'is_active' => true,
        ]);

        DepositMethod::create([
            'currency' => 'SOL',
            'name' => 'Solana (SOL)',
            'network' => 'Solana Network',
            'wallet_address' => 'So11111111111111111111111111111111111111112',
            'min_deposit' => '0.1 SOL',
            'confirmations_required' => 1,
            'notes' => 'Send only SOL to this Solana wallet address. Fast instant crediting.',
            'is_active' => true,
        ]);

        // 5. Coins
        $coinsData = [
            [
                'name' => 'PEPEKING',
                'ticker' => 'PEPE',
                'description' => 'The king of all Pepes. Built for the meme revolution with simulated zero tax and high bonding curve momentum.',
                'logo_path' => 'pepeking.png',
                'contract_address' => '8x1234abcd5678ef90gh12ij34kl56mn78',
                'network' => 'Solana',
                'current_price' => 0.000245,
                'initial_price' => 0.000185,
                'market_cap' => 2450000,
                'change_24h' => 32.45,
                'volume_24h' => 1245230,
                'holders_count' => 12652,
                'buyers_count' => 14231,
                'liquidity' => 245000,
                'total_supply' => 10000000000,
                'is_featured' => true,
                'is_trending' => true,
                'price_movement_mode' => 'auto_volatile',
                'auto_step_percent' => 1.80,
                'auto_interval_seconds' => 10,
            ],
            [
                'name' => 'DOGEMOON',
                'ticker' => 'DOGE',
                'description' => 'Doge takes the rocket straight to outer orbit. Community driven cosmic dog meme token.',
                'logo_path' => 'dogemoon.png',
                'contract_address' => '7d9834abcd1234ef90gh12ij34kl56mn11',
                'network' => 'Solana',
                'current_price' => 0.000187,
                'initial_price' => 0.000158,
                'market_cap' => 1870000,
                'change_24h' => 18.23,
                'volume_24h' => 1870000,
                'holders_count' => 9720,
                'buyers_count' => 10840,
                'liquidity' => 180000,
                'total_supply' => 10000000000,
                'is_featured' => true,
                'is_trending' => true,
                'price_movement_mode' => 'auto_up',
                'auto_step_percent' => 1.20,
                'auto_interval_seconds' => 12,
            ],
            [
                'name' => 'SHIBABOOM',
                'ticker' => 'SHIBA',
                'description' => 'Explosive Shiba energy powering the next generation of meme liquidity.',
                'logo_path' => 'shibaboom.png',
                'contract_address' => '3f4434abcd5678ef90gh12ij34kl56mn22',
                'network' => 'Solana',
                'current_price' => 0.000032,
                'initial_price' => 0.000028,
                'market_cap' => 1220000,
                'change_24h' => 12.11,
                'volume_24h' => 1220000,
                'holders_count' => 6210,
                'buyers_count' => 7420,
                'liquidity' => 120000,
                'total_supply' => 40000000000,
                'is_featured' => true,
                'is_trending' => true,
                'price_movement_mode' => 'auto_volatile',
                'auto_step_percent' => 1.50,
                'auto_interval_seconds' => 15,
            ],
            [
                'name' => 'BABYFLOKI',
                'ticker' => 'FLOKI',
                'description' => 'Viking puppy on Solana with endless meme potential and community raids.',
                'logo_path' => 'babyfloki.png',
                'contract_address' => '5b8834abcd5678ef90gh12ij34kl56mn33',
                'network' => 'Solana',
                'current_price' => 0.000009,
                'initial_price' => 0.000008,
                'market_cap' => 890000,
                'change_24h' => 8.55,
                'volume_24h' => 890000,
                'holders_count' => 4140,
                'buyers_count' => 5120,
                'liquidity' => 90000,
                'total_supply' => 100000000000,
                'is_featured' => false,
                'is_trending' => true,
                'price_movement_mode' => 'auto_volatile',
                'auto_step_percent' => 2.00,
                'auto_interval_seconds' => 10,
            ],
            [
                'name' => 'WOJAK',
                'ticker' => 'WOJAK',
                'description' => 'Feels good man. The token for all crypto traders riding the rollercoaster of green and red candles.',
                'logo_path' => 'wojak.png',
                'contract_address' => '9a1134abcd5678ef90gh12ij34kl56mn44',
                'network' => 'Solana',
                'current_price' => 0.000113,
                'initial_price' => 0.000107,
                'market_cap' => 1130000,
                'change_24h' => 5.32,
                'volume_24h' => 1130000,
                'holders_count' => 8110,
                'buyers_count' => 9300,
                'liquidity' => 110000,
                'total_supply' => 10000000000,
                'is_featured' => false,
                'is_trending' => true,
                'price_movement_mode' => 'auto_volatile',
                'auto_step_percent' => 1.40,
                'auto_interval_seconds' => 14,
            ],
            // New Launches
            [
                'name' => 'CATINU',
                'ticker' => 'CATI',
                'description' => 'A feline takeover of the doge empire. 100% fair launch on Pump Endless.',
                'logo_path' => 'catinu.png',
                'contract_address' => '2c2234abcd5678ef90gh12ij34kl56mn55',
                'network' => 'Solana',
                'current_price' => 0.000012,
                'initial_price' => 0.000010,
                'market_cap' => 121000,
                'change_24h' => 21.00,
                'volume_24h' => 12100,
                'holders_count' => 1240,
                'buyers_count' => 1650,
                'liquidity' => 25000,
                'total_supply' => 10000000000,
                'is_featured' => false,
                'is_trending' => false,
                'price_movement_mode' => 'auto_up',
                'auto_step_percent' => 2.50,
                'auto_interval_seconds' => 10,
            ],
            [
                'name' => 'TURBOINU',
                'ticker' => 'TURBO',
                'description' => 'Supercharged AI-engineered pup going fast and breaking speed barriers.',
                'logo_path' => 'turboinu.png',
                'contract_address' => '4t4434abcd5678ef90gh12ij34kl56mn66',
                'network' => 'Solana',
                'current_price' => 0.000018,
                'initial_price' => 0.000015,
                'market_cap' => 180000,
                'change_24h' => 9.20,
                'volume_24h' => 12100,
                'holders_count' => 2100,
                'buyers_count' => 2800,
                'liquidity' => 30000,
                'total_supply' => 10000000000,
                'is_featured' => false,
                'is_trending' => false,
                'price_movement_mode' => 'auto_volatile',
                'auto_step_percent' => 1.80,
                'auto_interval_seconds' => 10,
            ],
            [
                'name' => 'MONKEYX',
                'ticker' => 'MKX',
                'description' => 'Return to monke. The wildest ape on Solana with bananomics built in.',
                'logo_path' => 'monkeyx.png',
                'contract_address' => '6m6634abcd5678ef90gh12ij34kl56mn77',
                'network' => 'Solana',
                'current_price' => 0.000008,
                'initial_price' => 0.000007,
                'market_cap' => 87000,
                'change_24h' => 8.70,
                'volume_24h' => 18200,
                'holders_count' => 870,
                'buyers_count' => 1120,
                'liquidity' => 15000,
                'total_supply' => 10000000000,
                'is_featured' => false,
                'is_trending' => false,
                'price_movement_mode' => 'auto_volatile',
                'auto_step_percent' => 2.20,
                'auto_interval_seconds' => 12,
            ],
            [
                'name' => 'PEPEX2',
                'ticker' => 'PEPE2',
                'description' => 'Because one Pepe was simply not enough to satisfy the degen universe.',
                'logo_path' => 'pepex2.png',
                'contract_address' => '7p7734abcd5678ef90gh12ij34kl56mn88',
                'network' => 'Solana',
                'current_price' => 0.000030,
                'initial_price' => 0.000028,
                'market_cap' => 63000,
                'change_24h' => 6.30,
                'volume_24h' => 15300,
                'holders_count' => 1450,
                'buyers_count' => 1890,
                'liquidity' => 12000,
                'total_supply' => 2000000000,
                'is_featured' => false,
                'is_trending' => false,
                'price_movement_mode' => 'auto_volatile',
                'auto_step_percent' => 1.50,
                'auto_interval_seconds' => 15,
            ],
            [
                'name' => 'ZILLY',
                'ticker' => 'ZILLY',
                'description' => 'The pink menace taking over the Solana ecosystem with unstoppable meme energy.',
                'logo_path' => 'zilly.svg',
                'contract_address' => '8z8834abcd5678ef90gh12ij34kl56mn99',
                'network' => 'Solana',
                'current_price' => 0.000083,
                'initial_price' => 0.000077,
                'market_cap' => 83000,
                'change_24h' => 8.30,
                'volume_24h' => 18300,
                'holders_count' => 1980,
                'buyers_count' => 2340,
                'liquidity' => 16000,
                'total_supply' => 1000000000,
                'is_featured' => false,
                'is_trending' => false,
                'price_movement_mode' => 'auto_up',
                'auto_step_percent' => 1.70,
                'auto_interval_seconds' => 10,
            ],
        ];

        $createdCoins = [];
        foreach ($coinsData as $data) {
            $createdCoins[$data['ticker']] = Coin::create($data);
        }

        $pepeCoin = $createdCoins['PEPE'];

        // 6. Seed Simulated Recent Trades for PEPEKING (matching coin-page.png)
        $simulatedTrades = [
            ['wallet' => '0x8f4d9a1b2c3d4e5f', 'type' => 'buy', 'usd' => 245.32, 'tokens' => 1250000000, 'time' => 2],
            ['wallet' => '0x3e8a1c4d5b6f7a8b', 'type' => 'buy', 'usd' => 125.00, 'tokens' => 640200000, 'time' => 5],
            ['wallet' => '0x16ae7f8a9b0c1d2e', 'type' => 'sell', 'usd' => 532.10, 'tokens' => 2650000000, 'time' => 8],
            ['wallet' => '0x9c3d4b1d2e3f4a5b', 'type' => 'buy', 'usd' => 75.32, 'tokens' => 385600000, 'time' => 11],
            ['wallet' => '0x4d5e6f6a7b8c9d0e', 'type' => 'sell', 'usd' => 230.00, 'tokens' => 1150000000, 'time' => 15],
            ['wallet' => '0x7b8c9d0e1f2a3b4c', 'type' => 'buy', 'usd' => 450.00, 'tokens' => 1836000000, 'time' => 22],
            ['wallet' => '0x2a3b4c5d6e7f8a9b', 'type' => 'buy', 'usd' => 890.50, 'tokens' => 3634000000, 'time' => 35],
            ['wallet' => '0x5e6f7a8b9c0d1e2f', 'type' => 'sell', 'usd' => 310.20, 'tokens' => 1266000000, 'time' => 50],
        ];

        foreach ($simulatedTrades as $trade) {
            CoinTrade::create([
                'coin_id' => $pepeCoin->id,
                'wallet_address' => $trade['wallet'],
                'type' => $trade['type'],
                'token_amount' => $trade['tokens'],
                'usd_amount' => $trade['usd'],
                'price' => $trade['usd'] / ($trade['tokens'] > 0 ? $trade['tokens'] : 1),
                'created_at' => Carbon::now()->subSeconds($trade['time']),
            ]);
        }

        // 8. Simulated trades for other coins
        foreach ($createdCoins as $ticker => $c) {
            if ($ticker === 'PEPE') {
                continue;
            }
            for ($i = 0; $i < 4; $i++) {
                $isBuy = ($i % 3 !== 0);
                $usd = rand(50, 600);
                CoinTrade::create([
                    'coin_id' => $c->id,
                    'wallet_address' => '0x'.substr(md5(rand()), 0, 16),
                    'type' => $isBuy ? 'buy' : 'sell',
                    'token_amount' => $usd / $c->current_price,
                    'usd_amount' => $usd,
                    'price' => $c->current_price,
                    'created_at' => Carbon::now()->subMinutes(rand(1, 30)),
                ]);
            }
        }

        // 9. Candlestick Historical Data Generator for PEPEKING & others
        foreach ($createdCoins as $coin) {
            $basePrice = $coin->current_price;
            // Generate 35 realistic candles for 1D timeframe
            $curr = $basePrice * 0.55; // start lower
            for ($i = 35; $i >= 0; $i--) {
                $candleTime = Carbon::now()->subDays($i);
                $variation = (rand(-5, 9) / 100);
                $open = $curr;
                $close = $curr * (1 + $variation);
                if ($i === 0) {
                    $close = $coin->current_price;
                }
                $high = max($open, $close) * (1 + (rand(1, 4) / 100));
                $low = min($open, $close) * (1 - (rand(1, 4) / 100));
                $vol = rand(20000, 150000);

                PriceHistory::create([
                    'coin_id' => $coin->id,
                    'timeframe' => '1D',
                    'open' => $open,
                    'high' => $high,
                    'low' => $low,
                    'close' => $close,
                    'volume' => $vol,
                    'candle_time' => $candleTime,
                ]);

                $curr = $close;
            }

            // Generate 24 candles for 1h
            $curr = $basePrice * 0.92;
            for ($i = 24; $i >= 0; $i--) {
                $candleTime = Carbon::now()->subHours($i);
                $variation = (rand(-3, 4) / 100);
                $open = $curr;
                $close = $curr * (1 + $variation);
                if ($i === 0) {
                    $close = $coin->current_price;
                }
                $high = max($open, $close) * (1 + (rand(1, 2) / 100));
                $low = min($open, $close) * (1 - (rand(1, 2) / 100));
                $vol = rand(5000, 45000);

                PriceHistory::create([
                    'coin_id' => $coin->id,
                    'timeframe' => '1h',
                    'open' => $open,
                    'high' => $high,
                    'low' => $low,
                    'close' => $close,
                    'volume' => $vol,
                    'candle_time' => $candleTime,
                ]);

                $curr = $close;
            }
        }
    }
}
