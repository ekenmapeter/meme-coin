<?php

namespace Tests\Feature;

use App\Models\UserHolding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_without_coins(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_home_page_shows_active_coins(): void
    {
        $this->createCoin();

        $this->get('/')
            ->assertOk()
            ->assertSee('PEPEKING');
    }

    public function test_authenticated_users_get_mobile_bottom_nav_and_drawer(): void
    {
        $user = $this->createUser();
        $this->createCoin();

        $this->actingAs($user)->get('/')
            ->assertOk()
            ->assertSee('bottom-nav', false)
            ->assertSee('drawer-panel', false)
            ->assertSee('x-data', false)
            ->assertSee('fa-solid', false)
            ->assertSee('$0.00', false);
    }

    public function test_guests_do_not_get_bottom_nav(): void
    {
        $this->get('/')->assertOk()->assertDontSee('bottom-nav', false);
    }

    public function test_all_coins_page_shows_active_coins(): void
    {
        $this->createCoin();

        $this->get(route('coins.index'))
            ->assertOk()
            ->assertSee('PEPEKING')
            ->assertSee('All Coins');
    }

    public function test_home_leaderboard_shows_only_top_coins_with_view_all_link(): void
    {
        $this->createCoin(['name' => 'COINA', 'ticker' => 'COINA']);
        $this->createCoin(['name' => 'COINB', 'ticker' => 'COINB']);
        $this->createCoin(['name' => 'COINC', 'ticker' => 'COINC']);
        $this->createCoin(['name' => 'COIND', 'ticker' => 'COIND']);
        $this->createCoin(['name' => 'COINE', 'ticker' => 'COINE']);
        $this->createCoin(['name' => 'COINF', 'ticker' => 'COINF']);

        $response = $this->get('/')->assertOk();

        // Leaderboard caps at 5 rows; the 6th coin only appears on /coins.
        $html = $response->getContent();
        $this->assertSame(5, substr_count($html, 'coin-leaderboard-row'));
        $this->assertStringContainsString('View All', $html);
        $this->assertStringContainsString('Show All', $html);
    }

    public function test_search_returns_coin_json(): void
    {
        $this->createCoin();

        $this->getJson(route('search', ['q' => 'PEPE']))
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_dashboard_requires_auth_and_renders_user_data(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));

        $user = $this->createUser(['name' => 'DashUser']);
        $coin = $this->createCoin();

        UserHolding::create([
            'user_id' => $user->id,
            'coin_id' => $coin->id,
            'token_balance' => 500000,
            'avg_buy_price' => 0.0002,
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('DashUser')
            ->assertSee('Your Holdings')
            ->assertSee('PEPEKING');
    }

    public function test_live_market_endpoint_returns_coin_data_and_totals(): void
    {
        $coin = $this->createCoin();

        $response = $this->getJson(route('api.market.live'))
            ->assertOk()
            ->assertJsonStructure([
                'coins' => [[
                    'ticker',
                    'name',
                    'price',
                    'formatted_price',
                    'change_24h',
                    'market_cap',
                    'formatted_market_cap',
                    'volume_24h',
                    'formatted_volume',
                    'holders_count',
                    'formatted_holders',
                ]],
                'sol_usd_price',
                'total_market_cap',
                'total_volume_24h',
                'total_coins',
                'updated_at',
            ]);

        $json = $response->json();
        $this->assertSame($coin->ticker, $json['coins'][0]['ticker']);
        $this->assertSame(1, $json['total_coins']);
    }

    public function test_live_market_endpoint_ticks_due_auto_coins(): void
    {
        $auto = $this->createCoin(['ticker' => 'AUTO', 'price_movement_mode' => 'auto_up', 'last_auto_tick_at' => null]);
        $this->createCoin(['ticker' => 'PEPE', 'name' => 'PEPEKING']);

        $this->getJson(route('api.market.live'))->assertOk();

        $this->assertNotSame(0.000245, (float) $auto->refresh()->current_price);
    }
}
