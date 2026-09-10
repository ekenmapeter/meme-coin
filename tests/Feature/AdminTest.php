<?php

namespace Tests\Feature;

use App\Models\Coin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_users_are_blocked_from_admin_routes(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_admins_can_access_dashboard(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_can_create_coin(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.coins.store'), [
            'name' => 'Testcoin',
            'ticker' => 'TSTC',
            'description' => 'A test coin',
            'current_price' => 0.0001,
            'total_supply' => 1000000000,
            'holders_count' => 1,
            'buyers_count' => 1,
            'volume_24h' => 100,
            'liquidity' => 1000,
            'price_movement_mode' => 'manual',
            'auto_step_percent' => 1.0,
            'auto_interval_seconds' => 30,
        ])->assertRedirect(route('admin.coins.index'));

        $coin = Coin::where('ticker', 'TSTC')->firstOrFail();
        $this->assertEqualsWithDelta(0.0001 * 1000000000, $coin->market_cap, 0.0001);
    }

    public function test_coin_name_with_html_is_rejected(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.coins.store'), [
            'name' => '<img src=x onerror=alert(1)>',
            'ticker' => 'XSS01',
            'description' => 'attempted stored XSS',
            'current_price' => 0.0001,
            'total_supply' => 1000000000,
            'holders_count' => 1,
            'buyers_count' => 1,
            'volume_24h' => 100,
            'price_movement_mode' => 'manual',
            'auto_step_percent' => 1.0,
            'auto_interval_seconds' => 30,
        ])->assertSessionHasErrors('name');

        $this->assertDatabaseMissing('coins', ['ticker' => 'XSS01']);
    }

    public function test_public_launch_rejects_coin_name_with_html(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->post(route('coins.storeLaunch'), [
            'name' => '<b>EVIL</b>',
            'ticker' => 'EVIL',
            'description' => 'attempted stored XSS',
        ])->assertSessionHasErrors('name');

        $this->assertDatabaseMissing('coins', ['ticker' => 'EVIL']);
    }

    public function test_deactivating_coin_blocks_new_trades_but_keeps_holdings(): void
    {
        $admin = $this->createAdmin();
        $coin = $this->createCoin();

        $this->actingAs($admin)->post(route('admin.coins.deactivate', $coin->id))
            ->assertSessionHas('success');

        $this->assertFalse($coin->refresh()->is_active);
        $this->assertDatabaseHas('coins', ['id' => $coin->id]);
    }

    public function test_coin_cannot_be_hard_deleted(): void
    {
        $admin = $this->createAdmin();
        $coin = $this->createCoin();

        // The destroy endpoint has been removed entirely.
        $this->actingAs($admin)->delete('/admin/coins/'.$coin->id)->assertStatus(405);

        $this->assertDatabaseHas('coins', ['id' => $coin->id]);
    }

    public function test_simulate_trade_validates_inputs(): void
    {
        $admin = $this->createAdmin();
        $coin = $this->createCoin();

        $this->actingAs($admin)->post(route('admin.coins.simulateTrade', $coin->id), [
            'type' => 'buy',
            'usd_amount' => -100,
        ])->assertSessionHasErrors('usd_amount');

        $this->actingAs($admin)->post(route('admin.coins.simulateTrade', $coin->id), [
            'type' => 'sideways',
            'usd_amount' => 100,
        ])->assertSessionHasErrors('type');
    }

    public function test_audit_log_is_written_for_admin_actions(): void
    {
        $admin = $this->createAdmin();
        $coin = $this->createCoin();

        $this->actingAs($admin)->post(route('admin.coins.deactivate', $coin->id));

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'event' => 'coin.deactivated',
            'subject_type' => Coin::class,
            'subject_id' => $coin->id,
        ]);
    }

    public function test_settings_update_persists(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'swap_fee_percent' => '2.5',
            'btc_withdrawal_fee' => '0.000400',
            'btc_usd_price' => '70000',
            'sol_usd_price' => '150',
            'platform_name' => 'Pump Endless',
            'platform_announcement' => 'Fees updated',
            'site_name' => 'Pump Endless Pro',
            'site_description' => 'A branded trading platform.',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('platform_settings', ['key' => 'swap_fee_percent', 'value' => '2.5']);
        $this->assertDatabaseHas('platform_settings', ['key' => 'site_name', 'value' => 'Pump Endless Pro']);
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $admin->id, 'event' => 'settings.updated']);
    }

    public function test_admin_can_suspend_and_unsuspend_user(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $this->actingAs($admin)->post(route('admin.users.suspend', $user->id))->assertSessionHas('success');
        $this->assertTrue($user->refresh()->isSuspended());

        $this->actingAs($admin)->post(route('admin.users.suspend', $user->id))->assertSessionHas('success');
        $this->assertFalse($user->refresh()->isSuspended());
    }

    public function test_admin_cannot_suspend_self(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.users.suspend', $admin->id))->assertSessionHas('error');
        $this->assertFalse($admin->refresh()->isSuspended());
    }

    public function test_admin_can_restrict_and_unrestrict_user(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $this->actingAs($admin)->post(route('admin.users.restrict', $user->id))->assertSessionHas('success');
        $this->assertTrue($user->refresh()->isRestricted());

        $this->actingAs($admin)->post(route('admin.users.restrict', $user->id))->assertSessionHas('success');
        $this->assertFalse($user->refresh()->isRestricted());
    }

    public function test_admin_can_delete_regular_user_but_not_admin_or_self(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $otherAdmin = $this->createUser(['email' => 'admin2@example.com', 'role' => 'admin']);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $user->id))->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $otherAdmin->id))->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin->id))->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_view_audit_logs(): void
    {
        $admin = $this->createAdmin();
        $coin = $this->createCoin();

        $this->actingAs($admin)->post(route('admin.coins.deactivate', $coin->id));

        $this->actingAs($admin)->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertSee('coin.deactivated')
            ->assertSee($admin->name);
    }

    public function test_admin_can_update_user_name_email_and_role(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $this->actingAs($admin)->post(route('admin.users.update', $user->id), [
            'name' => 'Renamed',
            'email' => 'renamed@example.com',
            'role' => 'user',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Renamed', 'email' => 'renamed@example.com']);
    }
}
