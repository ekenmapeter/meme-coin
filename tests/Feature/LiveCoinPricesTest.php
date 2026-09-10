<?php

namespace Tests\Feature;

use App\Services\MarketSimulatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LiveCoinPricesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.crypto_prices.enabled' => true]);
        Cache::flush();
    }

    public function test_sync_updates_price_and_market_stats_from_live_feed(): void
    {
        Http::fake([
            '*/coins/markets*' => Http::response([
                [
                    'id' => 'pepe',
                    'current_price' => 0.0000123,
                    'market_cap' => 5000000000,
                    'total_volume' => 150000000,
                    'price_change_percentage_24h' => 8.25,
                ],
            ]),
        ]);

        $coin = $this->createCoin(['coingecko_id' => 'pepe']);

        app(MarketSimulatorService::class)->syncLiveCoinPrices();

        $coin->refresh();
        $this->assertSame(0.0000123, $coin->current_price);
        $this->assertSame(5000000000.0, $coin->market_cap);
        $this->assertSame(150000000.0, $coin->volume_24h);
        $this->assertSame(8.25, $coin->change_24h);
    }

    public function test_sync_ignores_coins_missing_from_the_feed(): void
    {
        Http::fake(['*/coins/markets*' => Http::response([])]);

        $coin = $this->createCoin(['coingecko_id' => 'does-not-exist']);
        $oldPrice = $coin->current_price;

        app(MarketSimulatorService::class)->syncLiveCoinPrices();

        $this->assertSame($oldPrice, $coin->refresh()->current_price);
    }

    public function test_auto_tick_skips_coins_with_live_prices(): void
    {
        $coin = $this->createCoin([
            'coingecko_id' => 'pepe',
            'price_movement_mode' => 'auto_up',
        ]);
        $oldPrice = $coin->current_price;

        app(MarketSimulatorService::class)->tickCoin($coin);

        $this->assertFalse(app(MarketSimulatorService::class)->tickCoin($coin->refresh()));
        $this->assertSame($oldPrice, $coin->refresh()->current_price);
    }

    public function test_coin_without_coingecko_id_stays_simulated(): void
    {
        $coin = $this->createCoin(['price_movement_mode' => 'auto_up']);

        $this->assertTrue(app(MarketSimulatorService::class)->tickCoin($coin));
    }
}
