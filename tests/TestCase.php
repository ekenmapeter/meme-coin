<?php

namespace Tests;

use App\Models\Coin;
use App\Models\DepositMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function createUser(array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => User::ROLE_USER,
        ], $overrides));

        // Balances are not mass-assignable; default every test user to zero funds.
        $user->forceFill([
            'sol_balance' => $overrides['sol_balance'] ?? 0.0,
            'btc_balance' => $overrides['btc_balance'] ?? 0.0,
            'usd_balance' => $overrides['usd_balance'] ?? 0.0,
        ])->save();

        return $user;
    }

    protected function createAdmin(array $overrides = []): User
    {
        return $this->createUser(array_merge([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => User::ROLE_ADMIN,
        ], $overrides));
    }

    protected function createCoin(array $overrides = []): Coin
    {
        return Coin::create(array_merge([
            'name' => 'PEPEKING',
            'ticker' => 'PEPE',
            'description' => 'Test coin',
            'logo_path' => 'pepeking.png',
            'contract_address' => '8x'.bin2hex(random_bytes(16)),
            'network' => 'Solana',
            'current_price' => 0.000245,
            'initial_price' => 0.000185,
            'market_cap' => 2450000,
            'change_24h' => 10.0,
            'volume_24h' => 100000,
            'holders_count' => 100,
            'buyers_count' => 80,
            'liquidity' => 10000,
            'total_supply' => 10000000000,
            'is_featured' => true,
            'is_trending' => true,
            'is_active' => true,
            'price_movement_mode' => 'manual',
            'auto_step_percent' => 1.5,
            'auto_interval_seconds' => 15,
        ], $overrides));
    }

    protected function createDepositMethod(array $overrides = []): DepositMethod
    {
        return DepositMethod::create(array_merge([
            'currency' => 'BTC',
            'name' => 'Bitcoin (BTC)',
            'network' => 'Bitcoin Network',
            'wallet_address' => 'bc1qtestaddress0000000000000000000',
            'min_deposit' => '0.001 BTC',
            'confirmations_required' => 3,
            'notes' => null,
            'is_active' => true,
        ], $overrides));
    }
}
