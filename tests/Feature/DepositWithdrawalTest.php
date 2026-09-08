<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\PlatformSetting;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepositWithdrawalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        PlatformSetting::set('btc_withdrawal_fee', '0.000300');
    }

    public function test_guests_cannot_submit_deposits(): void
    {
        $this->post(route('wallet.deposit'), [
            'deposit_method_id' => 1,
            'amount' => 0.1,
            'txid' => 'abc123def456ghi',
        ])->assertRedirect(route('login'));
    }

    public function test_user_can_submit_pending_deposit(): void
    {
        $user = $this->createUser();
        $method = $this->createDepositMethod();

        $this->actingAs($user)->post(route('wallet.deposit'), [
            'deposit_method_id' => $method->id,
            'amount' => 0.1,
            'txid' => 'abc123def456ghi',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('deposits', [
            'user_id' => $user->id,
            'currency' => 'BTC',
            'amount' => 0.1,
            'status' => 'pending',
        ]);
    }

    public function test_admin_confirmation_credits_btc_balance_exactly_once(): void
    {
        $user = $this->createUser();
        $admin = $this->createAdmin();
        $method = $this->createDepositMethod();

        $deposit = Deposit::create([
            'user_id' => $user->id,
            'deposit_method_id' => $method->id,
            'currency' => 'BTC',
            'amount' => 0.1,
            'txid' => 'abc123def456ghi',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.deposits.status', $deposit->id), ['status' => 'confirmed'])
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertEqualsWithDelta(0.1, $user->btc_balance, 0.00000001);

        // Re-confirming (or flipping back to pending then confirming) must not double-credit.
        $this->actingAs($admin)->post(route('admin.deposits.status', $deposit->id), ['status' => 'confirmed'])
            ->assertSessionHas('error');

        $user->refresh();
        $this->assertEqualsWithDelta(0.1, $user->btc_balance, 0.00000001);

        $this->actingAs($admin)->post(route('admin.deposits.status', $deposit->id), ['status' => 'rejected'])
            ->assertSessionHas('error');

        $user->refresh();
        $this->assertEqualsWithDelta(0.1, $user->btc_balance, 0.00000001);
    }

    public function test_admin_can_reject_pending_deposit_without_crediting(): void
    {
        $user = $this->createUser();
        $admin = $this->createAdmin();
        $method = $this->createDepositMethod();

        $deposit = Deposit::create([
            'user_id' => $user->id,
            'deposit_method_id' => $method->id,
            'currency' => 'BTC',
            'amount' => 0.1,
            'txid' => 'abc123def456ghi',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.deposits.status', $deposit->id), ['status' => 'rejected'])
            ->assertSessionHas('success');

        $this->assertEquals(0.0, $user->refresh()->btc_balance);
        $this->assertSame('rejected', $deposit->refresh()->status);
    }

    public function test_sol_deposits_credit_sol_balance(): void
    {
        $user = $this->createUser();
        $admin = $this->createAdmin();
        $method = $this->createDepositMethod(['currency' => 'SOL', 'name' => 'Solana (SOL)']);

        $deposit = Deposit::create([
            'user_id' => $user->id,
            'deposit_method_id' => $method->id,
            'currency' => 'SOL',
            'amount' => 5,
            'txid' => 'sol-txid-123456',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.deposits.status', $deposit->id), ['status' => 'confirmed'])
            ->assertSessionHas('success');

        $this->assertEqualsWithDelta(5.0, $user->refresh()->sol_balance, 0.000001);
        $this->assertEquals(0.0, $user->btc_balance);
    }

    public function test_deposits_with_unsupported_currency_are_rejected(): void
    {
        $user = $this->createUser();
        $admin = $this->createAdmin();
        $method = $this->createDepositMethod(['currency' => 'ETH', 'name' => 'Ethereum (ETH)']);

        $deposit = Deposit::create([
            'user_id' => $user->id,
            'deposit_method_id' => $method->id,
            'currency' => 'ETH',
            'amount' => 0.5,
            'txid' => 'eth-txid-123456',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.deposits.status', $deposit->id), ['status' => 'confirmed'])
            ->assertSessionHas('error');

        $this->assertSame('pending', $deposit->refresh()->status);
    }

    public function test_withdrawal_flow_deducts_balance_and_refunds_on_rejection(): void
    {
        $user = $this->createUser();
        $user->btc_balance = 1.0;
        $user->save();

        $this->actingAs($user)->post(route('wallet.withdraw'), [
            'destination_address' => 'bc1qxy2kgdygjrsztqz2n0yrf2493p831kkfjhx0wlh',
            'amount' => 0.1,
        ])->assertSessionHas('success');

        $user->refresh();
        $this->assertEqualsWithDelta(0.9, $user->btc_balance, 0.00000001);

        $withdrawal = Withdrawal::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('pending', $withdrawal->status);

        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.withdrawals.status', $withdrawal->id), ['status' => 'rejected'])
            ->assertSessionHas('success');

        $this->assertEqualsWithDelta(1.0, $user->refresh()->btc_balance, 0.00000001);
    }

    public function test_withdrawal_cannot_be_double_refunded(): void
    {
        $user = $this->createUser();
        $user->btc_balance = 1.0;
        $user->save();

        $this->actingAs($user)->post(route('wallet.withdraw'), [
            'destination_address' => 'bc1qxy2kgdygjrsztqz2n0yrf2493p831kkfjhx0wlh',
            'amount' => 0.1,
        ]);

        $withdrawal = Withdrawal::where('user_id', $user->id)->firstOrFail();
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.withdrawals.status', $withdrawal->id), ['status' => 'rejected'])
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertEqualsWithDelta(1.0, $user->btc_balance, 0.00000001);

        // rejected → approved / rejected again must be blocked by the state machine.
        $this->actingAs($admin)->post(route('admin.withdrawals.status', $withdrawal->id), ['status' => 'rejected'])
            ->assertSessionHas('error');
        $this->actingAs($admin)->post(route('admin.withdrawals.status', $withdrawal->id), ['status' => 'approved'])
            ->assertSessionHas('error');

        $this->assertEqualsWithDelta(1.0, $user->refresh()->btc_balance, 0.00000001);
    }

    public function test_completed_withdrawal_requires_txid_and_is_terminal(): void
    {
        $user = $this->createUser();
        $user->btc_balance = 1.0;
        $user->save();

        $this->actingAs($user)->post(route('wallet.withdraw'), [
            'destination_address' => 'bc1qxy2kgdygjrsztqz2n0yrf2493p831kkfjhx0wlh',
            'amount' => 0.1,
        ]);

        $withdrawal = Withdrawal::where('user_id', $user->id)->firstOrFail();
        $admin = $this->createAdmin();

        // Cannot jump pending → completed directly.
        $this->actingAs($admin)->post(route('admin.withdrawals.status', $withdrawal->id), ['status' => 'completed'])
            ->assertSessionHas('error');

        // Approve first.
        $this->actingAs($admin)->post(route('admin.withdrawals.status', $withdrawal->id), ['status' => 'approved'])
            ->assertSessionHas('success');

        // Complete without txid → error.
        $this->actingAs($admin)->post(route('admin.withdrawals.status', $withdrawal->id), ['status' => 'completed'])
            ->assertSessionHas('error');

        // Complete with txid → success, then terminal.
        $this->actingAs($admin)->post(route('admin.withdrawals.status', $withdrawal->id), [
            'status' => 'completed',
            'txid' => 'real-broadcast-txid-123',
        ])->assertSessionHas('success');

        $withdrawal->refresh();
        $this->assertSame('completed', $withdrawal->status);
        $this->assertSame('real-broadcast-txid-123', $withdrawal->txid);

        // completed is terminal: cannot be rejected/refunded after payout.
        $this->actingAs($admin)->post(route('admin.withdrawals.status', $withdrawal->id), ['status' => 'rejected'])
            ->assertSessionHas('error');

        $user->refresh();
        $this->assertEqualsWithDelta(0.9, $user->btc_balance, 0.00000001);
    }

    public function test_withdrawal_rejects_amount_at_or_below_fee(): void
    {
        $user = $this->createUser();
        $user->btc_balance = 1.0;
        $user->save();

        $this->actingAs($user)->post(route('wallet.withdraw'), [
            'destination_address' => 'bc1qxy2kgdygjrsztqz2n0yrf2493p831kkfjhx0wlh',
            'amount' => 0.0002,
        ])->assertSessionHas('error');

        $this->assertEqualsWithDelta(1.0, $user->refresh()->btc_balance, 0.00000001);
    }

    public function test_withdrawal_rejects_insufficient_balance(): void
    {
        $user = $this->createUser();
        $user->btc_balance = 0.01;
        $user->save();

        $this->actingAs($user)->post(route('wallet.withdraw'), [
            'destination_address' => 'bc1qxy2kgdygjrsztqz2n0yrf2493p831kkfjhx0wlh',
            'amount' => 5,
        ])->assertSessionHas('error');

        $this->assertEqualsWithDelta(0.01, $user->refresh()->btc_balance, 0.00000001);
    }
}
