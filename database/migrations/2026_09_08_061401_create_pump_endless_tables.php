<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add columns to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('wallet_address')->nullable()->unique()->after('email');
            $table->decimal('sol_balance', 18, 6)->default(0)->after('wallet_address');
            $table->decimal('btc_balance', 18, 8)->default(0)->after('sol_balance');
            $table->decimal('usd_balance', 18, 2)->default(0)->after('btc_balance');
            $table->boolean('is_admin')->default(false)->after('password');
            $table->string('avatar')->nullable()->after('is_admin');
        });

        // Coins table
        Schema::create('coins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ticker')->unique();
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('contract_address')->unique();
            $table->string('network')->default('Solana');
            $table->decimal('current_price', 24, 10)->default(0.000245);
            $table->decimal('initial_price', 24, 10)->default(0.000100);
            $table->decimal('market_cap', 24, 2)->default(2450000);
            $table->decimal('change_24h', 10, 2)->default(32.45);
            $table->decimal('volume_24h', 24, 2)->default(1245230);
            $table->unsignedBigInteger('holders_count')->default(12652);
            $table->unsignedBigInteger('buyers_count')->default(14231);
            $table->decimal('liquidity', 24, 2)->default(245000);
            $table->decimal('total_supply', 28, 2)->default(10000000000);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('price_movement_mode')->default('auto_volatile'); // manual, auto_up, auto_down, auto_volatile
            $table->decimal('auto_step_percent', 6, 2)->default(1.50);
            $table->integer('auto_interval_seconds')->default(15);
            $table->timestamp('last_auto_tick_at')->nullable();
            $table->timestamps();
        });

        // User Holdings
        Schema::create('user_holdings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('coin_id')->constrained()->onDelete('cascade');
            $table->decimal('token_balance', 28, 4)->default(0);
            $table->decimal('avg_buy_price', 24, 10)->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'coin_id']);
        });

        // Coin Trades (for live & user buy/sell tape)
        Schema::create('coin_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coin_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('wallet_address');
            $table->enum('type', ['buy', 'sell'])->default('buy');
            $table->decimal('token_amount', 28, 4);
            $table->decimal('usd_amount', 18, 2);
            $table->decimal('price', 24, 10);
            $table->timestamps();
        });

        // Price Candlestick / Historical data
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coin_id')->constrained()->onDelete('cascade');
            $table->string('timeframe')->default('1D'); // 1m, 5m, 15m, 1h, 4h, 1D
            $table->decimal('open', 24, 10);
            $table->decimal('high', 24, 10);
            $table->decimal('low', 24, 10);
            $table->decimal('close', 24, 10);
            $table->decimal('volume', 24, 2)->default(0);
            $table->timestamp('candle_time');
            $table->timestamps();
            $table->index(['coin_id', 'timeframe', 'candle_time']);
        });

        // Deposit Methods (BTC, ETH, USDT, SOL)
        Schema::create('deposit_methods', function (Blueprint $table) {
            $table->id();
            $table->string('currency'); // BTC, ETH, USDT, SOL
            $table->string('name'); // Bitcoin, Ethereum, Tether (TRC20), Solana
            $table->string('network'); // Bitcoin, ERC20, TRC20, Solana
            $table->string('wallet_address');
            $table->string('qr_code_url')->nullable();
            $table->string('min_deposit')->default('0.001');
            $table->integer('confirmations_required')->default(3);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // User Deposit Requests
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('deposit_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('currency');
            $table->decimal('amount', 18, 8);
            $table->string('txid');
            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        // User Withdrawal Requests
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('currency')->default('BTC');
            $table->decimal('amount', 18, 8);
            $table->decimal('network_fee', 18, 8)->default(0.0003);
            $table->decimal('net_amount', 18, 8);
            $table->string('destination_address');
            $table->enum('status', ['pending', 'approved', 'completed', 'rejected'])->default('pending');
            $table->string('txid')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        // Swaps (Meme coin -> BTC)
        Schema::create('swaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('coin_id')->constrained()->onDelete('cascade');
            $table->decimal('token_amount', 28, 4);
            $table->decimal('usd_value', 18, 2);
            $table->decimal('btc_amount_gross', 18, 8);
            $table->decimal('fee_percent', 5, 2)->default(1.00);
            $table->decimal('fee_btc', 18, 8);
            $table->decimal('net_btc_received', 18, 8);
            $table->decimal('rate', 24, 14); // 1 TOKEN = X BTC
            $table->string('status')->default('completed');
            $table->timestamps();
        });

        // Platform Settings
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
        Schema::dropIfExists('swaps');
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('deposit_methods');
        Schema::dropIfExists('price_histories');
        Schema::dropIfExists('coin_trades');
        Schema::dropIfExists('user_holdings');
        Schema::dropIfExists('coins');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['wallet_address', 'sol_balance', 'btc_balance', 'usd_balance', 'is_admin', 'avatar']);
        });
    }
};
