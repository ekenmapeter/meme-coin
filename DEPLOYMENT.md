# Deployment Guide — Pump Endless

This guide covers deploying the meme-coin launch & trading platform to a public server.

## 1. Server requirements

- PHP **8.2+** with extensions: `pdo_mysql`, `mbstring`, `xml`, `curl`, `gd` (or `imagick`), `fileinfo`
- Composer 2
- MySQL 8 (recommended) or SQLite
- Web server: Nginx or Apache, with PHP-FPM
- A cron scheduler available

## 2. Files & permissions

```bash
# upload the project (excluding vendor/, node_modules/, .env, storage/logs)
composer install --no-dev --optimize-autoloader

# storage must be writable by the web user
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

## 3. Environment configuration

```bash
cp .env.example .env
php artisan key:generate
```

Set at minimum:

| Key | Value |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://your-domain.example` |
| `DB_CONNECTION/DB_DATABASE/DB_USERNAME/DB_PASSWORD` | your MySQL credentials |
| `SESSION_DRIVER` | `database` |
| `SESSION_SECURE_COOKIE` | `true` (only when HTTPS is configured) |
| `TRUSTED_PROXIES` | your reverse proxy IP/CIDR (e.g. hosting proxy, Cloudflare) when applicable |
| `FORCE_HTTPS` | `true` when serving over TLS (requires `TRUSTED_PROXIES` behind a proxy) |

## 4. Database

```bash
php artisan migrate --force
php artisan db:seed --force     # seeds coins, candles, deposit methods, settings
php artisan admin:create --email=you@example.com --password='a-strong-password'
```

There is **no default admin** — create one with the command above.
New users register with zero balances; funds are granted only through admin-confirmed
deposits or admin balance adjustments.

## 5. Scheduler (required for the market engine)

Add a cron entry:

```
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

This runs `market:tick` every minute, advancing auto-movement prices. Without it, coin
prices only move when a user visits a coin page.

## 6. Queue

The app uses the `database` queue driver. Start a worker (or use Supervisor/systemd):

```bash
php artisan queue:work --tries=3
```

## 7. Cache & optimization

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> After any change to `.env`, run `php artisan config:clear` then `config:cache` again.

## 8. HTTPS & security headers

- Terminate TLS at your reverse proxy and set `TRUSTED_PROXIES` accordingly.
- The app sends these headers on every response (see `app/Http/Middleware/SecurityHeaders.php`):
  `X-Content-Type-Options`, `X-Frame-Options: DENY`, `Referrer-Policy`, `Permissions-Policy`,
  and `Strict-Transport-Security` (HSTS) on secure responses.
- Enable `FORCE_HTTPS=true` so every insecure request is redirected to HTTPS
  (`app/Http/Middleware/ForceHttps.php`, opt-in via config to avoid redirect loops).
- Auth endpoints are rate-limited (5 attempts/minute per IP+email, see `AppServiceProvider`).
- Trading, swap, deposit and withdrawal endpoints are rate-limited (`throttle:trades` /
  `throttle:wallet`), and public polling endpoints (`/api/market/live`, charts, search) are
  rate-limited (`throttle:public`).
- Password policy: minimum 8 characters with letters and numbers
  (`Illuminate\Validation\Rules\Password` defaults, `AppServiceProvider`).
- A full Content-Security-Policy is intentionally **not** shipped because the frontend
  uses inline scripts and CDN libraries; add one at the reverse-proxy layer if you can
  refactor the frontend to use external files.

## 9. Production do/don't

- Do: keep `APP_DEBUG=false`, `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`, HTTPS-only cookies.
- Don't: commit `.env`, `vendor/`, or `storage/logs` to the repository.
- Don't: ever re-introduce the removed one-click "connect as admin" or auto-login
  flows — both were removed for security (`AuthController`, `routes/web.php`).
- Don't: hard-delete coins — the delete endpoint was removed; deactivate instead
  (`admin.coins.deactivate`), which blocks new trades but preserves user holdings.

## 10. Operations

- Monitor `storage/logs/laravel.log`.
- Verify the scheduler: `php artisan schedule:list`.
- Check pending deposits/withdrawals in the admin panel; status transitions are strict:
  - Deposits: `pending → confirmed | rejected` (credit happens exactly once)
  - Withdrawals: `pending → approved → completed | rejected`; `completed` requires a txid and is terminal.