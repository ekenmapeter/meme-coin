<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminCoinController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDepositController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminWithdrawalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoinController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [HomeController::class, 'search'])->middleware('throttle:public')->name('search');
Route::get('/api/market/live', [HomeController::class, 'liveMarket'])->middleware('throttle:public')->name('api.market.live');

// Coin & Trading
Route::get('/coins', [CoinController::class, 'index'])->name('coins.index');
Route::get('/coins/{ticker}', [CoinController::class, 'show'])->name('coins.show');
Route::get('/api/coins/{ticker}/chart', [CoinController::class, 'chartData'])->middleware('throttle:public')->name('api.coins.chart');
Route::get('/api/coins/{ticker}/trades', [CoinController::class, 'trades'])->middleware('throttle:public')->name('api.coins.trades');
Route::post('/coins/{ticker}/trade', [CoinController::class, 'trade'])->middleware('throttle:trades')->name('coins.trade');

/*
|--------------------------------------------------------------------------
| Guest Routes (Auth)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth')->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'adminLogin'])->middleware('throttle:auth')->name('admin.login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes (Wallet, Swap, Deposit, Withdrawal)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/swap', function () {
        return redirect()->route('wallet.index', ['tab' => 'swap']);
    })->name('swap');
    Route::get('/deposit', function () {
        return redirect()->route('wallet.index', ['tab' => 'deposit']);
    })->name('deposit');
    Route::get('/withdraw', function () {
        return redirect()->route('wallet.index', ['tab' => 'withdraw']);
    })->name('withdraw');

    Route::get('/api/swap/quote', [WalletController::class, 'swapQuote'])->middleware('throttle:wallet')->name('api.swap.quote');
    Route::post('/wallet/swap', [WalletController::class, 'submitSwap'])->middleware('throttle:wallet')->name('wallet.swap');
    Route::post('/wallet/deposit', [WalletController::class, 'submitDeposit'])->middleware('throttle:wallet')->name('wallet.deposit');
    Route::post('/wallet/withdraw', [WalletController::class, 'submitWithdrawal'])->middleware('throttle:wallet')->name('wallet.withdraw');

    // Coin launch requires an authenticated account so creator holdings can be granted
    Route::get('/launch', [CoinController::class, 'launch'])->name('coins.launch');
    Route::post('/launch', [CoinController::class, 'storeLaunch'])->name('coins.storeLaunch');
});

Route::get('/leaderboard', function () {
    return redirect()->route('home');
})->name('leaderboard');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.redirect');

    // Coins Management & Simulation Controls
    Route::resource('coins', AdminCoinController::class)->except(['show', 'destroy']);
    Route::post('/coins/{coin}/quick-price', [AdminCoinController::class, 'quickSetPrice'])->name('coins.quickPrice');
    Route::post('/coins/{coin}/simulate-trade', [AdminCoinController::class, 'simulateTrade'])->name('coins.simulateTrade');
    Route::post('/coins/{coin}/toggle-pause', [AdminCoinController::class, 'togglePause'])->name('coins.togglePause');
    Route::post('/coins/{coin}/toggle-feature', [AdminCoinController::class, 'toggleFeature'])->name('coins.toggleFeature');
    Route::post('/coins/{coin}/deactivate', [AdminCoinController::class, 'deactivate'])->name('coins.deactivate');

    // Deposits
    Route::get('/deposits', [AdminDepositController::class, 'index'])->name('deposits.index');
    Route::post('/deposits/{deposit}/status', [AdminDepositController::class, 'updateStatus'])->name('deposits.status');
    Route::post('/deposit-methods/{depositMethod}', [AdminDepositController::class, 'updateMethod'])->name('deposit-methods.update');

    // Withdrawals
    Route::get('/withdrawals', [AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('/withdrawals/{withdrawal}/status', [AdminWithdrawalController::class, 'updateStatus'])->name('withdrawals.status');

    // Settings & Fees
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');

    // Audit Logs
    Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');

    // Users
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::post('/users/{user}/balance', [AdminUserController::class, 'updateBalance'])->name('users.balance');
    Route::post('/users/{user}/update', [AdminUserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/suspend', [AdminUserController::class, 'toggleSuspend'])->name('users.suspend');
    Route::post('/users/{user}/restrict', [AdminUserController::class, 'toggleRestrict'])->name('users.restrict');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
});
