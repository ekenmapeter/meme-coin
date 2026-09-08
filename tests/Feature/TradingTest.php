<?php

namespace Tests\Feature;

use App\Models\PlatformSetting;
use App\Models\UserHolding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        PlatformSetting::set('sol_usd_price', '142.50');
        PlatformSetting::set('btc_usd_price', '66450.00');
        PlatformSetting::set('swap_fee_percent', '1.00');
        PlatformSetting::set('btc_withdrawal_fee', '0.000300');
    }

    public function test_guest_cannot_trade(): void
    {
        $coin = $this->createCoin();

        $this->postJson(route('coins.trade', $coin->ticker), [
            'type' => 'buy',
            'amount' => 1,
            'currency' => 'SOL',
        ])->assertStatus(401);
    }

    public function test_buy_deducts_sol_and_credits_holdings(): void
    {
        $user = $this->createUser();
        $user->sol_balance = 100;
        $user->save();

        $coin = $this->createCoin();

        $response = $this->actingAs($user)->postJson(route('coins.trade', $coin->ticker), [
            'type' => 'buy',
            'amount' => 10,
            'currency' => 'SOL',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $user->refresh();
        $this->assertEqualsWithDelta(90.0, $user->sol_balance, 0.0001);

        $holding = UserHolding::where('user_id', $user->id)->where('coin_id', $coin->id)->first();
        $this->assertNotNull($holding);
        $this->assertGreaterThan(0, $holding->token_balance);
    }

    public function test_buy_rejects_insufficient_balance(): void
    {
        $user = $this->createUser();
        $coin = $this->createCoin();

        $response = $this->actingAs($user)->postJson(route('coins.trade', $coin->ticker), [
            'type' => 'buy',
            'amount' => 10,
            'currency' => 'SOL',
        ]);

        $response->assertStatus(422)->assertJson(['error' => true]);
        $this->assertDatabaseCount('user_holdings', 0);
    }

    public function test_sell_credits_sol_and_deducts_tokens(): void
    {
        $user = $this->createUser();
        $coin = $this->createCoin();

        $holding = UserHolding::create([
            'user_id' => $user->id,
            'coin_id' => $coin->id,
            'token_balance' => 1000000,
            'avg_buy_price' => 0.000200,
        ]);

        $user->sol_balance = 5;
        $user->save();

        $response = $this->actingAs($user)->postJson(route('coins.trade', $coin->ticker), [
            'type' => 'sell',
            'amount' => 500000,
            'currency' => 'SOL',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $holding->refresh();
        $this->assertEqualsWithDelta(500000, $holding->token_balance, 0.0001);
        $this->assertGreaterThan(5, $user->refresh()->sol_balance);
    }

    public function test_sell_rejects_insufficient_tokens(): void
    {
        $user = $this->createUser();
        $coin = $this->createCoin();

        $response = $this->actingAs($user)->postJson(route('coins.trade', $coin->ticker), [
            'type' => 'sell',
            'amount' => 500000,
            'currency' => 'SOL',
        ]);

        $response->assertStatus(422)->assertJson(['error' => true]);
    }

    public function test_trading_is_blocked_on_deactivated_coins(): void
    {
        $user = $this->createUser();
        $user->sol_balance = 100;
        $user->save();

        $coin = $this->createCoin(['is_active' => false]);

        $this->actingAs($user)->postJson(route('coins.trade', $coin->ticker), [
            'type' => 'buy',
            'amount' => 1,
            'currency' => 'SOL',
        ])->assertStatus(422)->assertJson(['error' => true]);
    }

    public function test_swap_converts_tokens_to_btc_minus_fee(): void
    {
        $user = $this->createUser();
        $coin = $this->createCoin(['current_price' => 0.000245]);

        UserHolding::create([
            'user_id' => $user->id,
            'coin_id' => $coin->id,
            'token_balance' => 1000000,
            'avg_buy_price' => 0.000200,
        ]);

        $this->actingAs($user)->post(route('wallet.swap'), [
            'coin_id' => $coin->id,
            'token_amount' => 1000000,
        ])->assertRedirect();

        $user->refresh();
        // 1,000,000 × 0.000245 = $245 gross → 0.0036869 BTC gross → 1% fee → net ≈ 0.003650 BTC
        $expectedNet = (1000000 * 0.000245 / 66450.00) * 0.99;
        $this->assertEqualsWithDelta($expectedNet, $user->btc_balance, 0.0000001);
    }

    public function test_swap_rejects_insufficient_tokens(): void
    {
        $user = $this->createUser();
        $coin = $this->createCoin();

        $this->actingAs($user)->post(route('wallet.swap'), [
            'coin_id' => $coin->id,
            'token_amount' => 999999999,
        ])->assertSessionHas('error');
    }
}
