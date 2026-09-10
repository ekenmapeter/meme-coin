<?php

namespace Tests\Feature;

use App\Models\PlatformSetting;
use App\Services\CryptoPriceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CryptoPriceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.crypto_prices.enabled' => true]);
        Cache::flush();
    }

    public function test_it_returns_live_prices_from_the_feed(): void
    {
        Http::fake([
            '*' => Http::response([
                'bitcoin' => ['usd' => 118500.25],
                'solana' => ['usd' => 201.75],
            ]),
        ]);

        PlatformSetting::set('btc_usd_price', '66450.00');
        PlatformSetting::set('sol_usd_price', '142.50');

        $service = app(CryptoPriceService::class);

        $this->assertSame(118500.25, $service->btcUsd());
        $this->assertSame(201.75, $service->solUsd());
    }

    public function test_it_falls_back_to_platform_settings_when_the_feed_fails(): void
    {
        Http::fake(['*' => Http::response('Server error', 500)]);

        PlatformSetting::set('btc_usd_price', '66450.00');
        PlatformSetting::set('sol_usd_price', '142.50');

        $service = app(CryptoPriceService::class);

        $this->assertSame(66450.0, $service->btcUsd());
        $this->assertSame(142.5, $service->solUsd());
    }

    public function test_it_caches_live_prices_between_calls(): void
    {
        Http::fake([
            '*' => Http::response([
                'bitcoin' => ['usd' => 118500.25],
                'solana' => ['usd' => 201.75],
            ]),
        ]);

        $service = app(CryptoPriceService::class);
        $service->btcUsd();
        $service->btcUsd();

        Http::assertSentCount(1);
    }
}
