# Pump Endless — Meme Coin Launch & Trading Platform

A high-speed meme-coin launch & trading platform built with Laravel 12.

## Features

- Browse and search meme coins with live-updating candlestick charts (Chart.js)
- Register / log in with email & password (no default credentials, no hidden backdoors)
- Buy/sell coins with SOL, swap meme tokens for BTC, request deposits/withdrawals
- Automated market engine with per-coin auto-movement controls and live CoinGecko
  price syncing for coins that have a `coingecko_id` configured
- Admin panel: coin management, price controls, deposit/withdrawal review, user balances, platform settings, audit logs
- Role-based access: `user` and `admin` roles (`app/Models/User.php`)
- Scheduled market tick (`php artisan market:tick`, every minute via the scheduler)

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

> New accounts register with zero balances. Funds arrive via admin-confirmed
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