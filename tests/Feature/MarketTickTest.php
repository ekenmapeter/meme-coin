<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketTickTest extends TestCase
{
    use RefreshDatabase;

    public function test_market_tick_command_runs_without_coins(): void
    {
        $this->artisan('market:tick')->assertSuccessful();
    }

    public function test_market_tick_ticks_auto_coins_only(): void
    {
        $manual = $this->createCoin(['price_movement_mode' => 'manual', 'last_auto_tick_at' => null]);
        $auto = $this->createCoin(['ticker' => 'AUTO', 'price_movement_mode' => 'auto_up', 'last_auto_tick_at' => null]);

        $this->artisan('market:tick')->assertSuccessful();

        $this->assertSame(0.000245, (float) $manual->refresh()->current_price);
        $this->assertNotSame(0.000245, (float) $auto->refresh()->current_price);
    }
}
