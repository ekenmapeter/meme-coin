# Pump Endless — Meme Coin Demo Platform

A high-speed **simulated** meme-coin launch & trading platform built with Laravel 12.

> **Important:** This is a demo/simulation platform. All prices, charts, market caps,
> balances, and trading activity are simulated. No real money, no real wallets, no real trades.

## Features

- Browse and search simulated meme coins with live-updating charts (Chart.js candles)
- Register / log in with email & password (no default credentials, no demo backdoors)
- Buy/sell coins with simulated SOL, swap meme tokens for BTC, request deposits/withdrawals
- Admin panel: coin management, price simulation controls, deposit/withdrawal review, user balances, platform settings
- Role-based access: `user` and `admin` roles (`app/Models/User.php`)
- Scheduled market simulation (`php artisan market:tick`, every minute via the scheduler)

## Requirements

- PHP >= 8.2
- Composer 2
- MySQL (or SQLite for local/testing)
- A cron job for the scheduler (production)

## Local Setup (XAMPP / dev)

```bash
composer install
cp .env.example .env          # configure your database
php artisan key:generate
php artisan migrate --seed    # seeds coins, candles, deposit methods & settings
php artisan serve
```

Create an administrator (no default admin exists):

```bash
php artisan admin:create --email=you@example.com --password='a-strong-password'
```

> New accounts register with **zero** simulated funds. Funds arrive via admin-confirmed
> deposits or admin balance adjustments — there is no free-credit registration.

## Production Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for the full checklist (HTTPS, cron, queue, config caching, security).

## Testing

```bash
php artisan test
```

The suite covers auth/roles, guest flows, trading math, swap fees, deposit/withdrawal
state machines (double-credit and double-refund guards), XSS input validation, and the market tick.

## License

MIT (Laravel framework license applies to framework code).