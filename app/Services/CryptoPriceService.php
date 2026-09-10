<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CryptoPriceService
{
    /**
     * CoinGecko ids tracked by the platform and the platform setting used
     * as a fallback whenever the live feed is disabled or unreachable.
     */
    protected const COINS = [
        'bitcoin' => ['setting' => 'btc_usd_price', 'default' => 66450.00],
        'solana' => ['setting' => 'sol_usd_price', 'default' => 142.50],
    ];

    protected const CACHE_KEY = 'crypto.live_usd_prices';

    protected const COINS_MARKET_CACHE_KEY = 'crypto.live_coins_market';

    public function btcUsd(): float
    {
        return $this->usd('bitcoin');
    }

    public function solUsd(): float
    {
        return $this->usd('solana');
    }

    /**
     * Live market data (price, market cap, 24h volume and change) for a set
     * of CoinGecko ids, keyed by id. Empty when the feed is disabled or down.
     *
     * @return array<string, array{price: float, market_cap: float, volume_24h: float, change_24h: float}>
     */
    public function coinMarketData(array $coingeckoIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('trim', $coingeckoIds))));
        if ($ids === []) {
            return [];
        }

        if (! config('services.crypto_prices.enabled')) {
            return [];
        }

        $data = Cache::remember(self::COINS_MARKET_CACHE_KEY, (int) config('services.crypto_prices.ttl', 60), function () use ($ids) {
            return $this->fetchCoinMarketData($ids);
        });

        // Only keep entries the caller asked for.
        return array_intersect_key($data, array_flip($ids));
    }

    /**
     * @return array<string, array{price: float, market_cap: float, volume_24h: float, change_24h: float}>
     */
    protected function fetchCoinMarketData(array $ids): array
    {
        try {
            $response = Http::timeout((int) config('services.crypto_prices.timeout', 5))
                ->retry(2, 200)
                ->get(config('services.crypto_prices.coins_markets_url'), [
                    'vs_currency' => 'usd',
                    'ids' => implode(',', $ids),
                    'per_page' => 250,
                ]);

            if (! $response->successful()) {
                Log::warning('Coin price feed returned HTTP '.$response->status());

                return [];
            }

            $result = [];
            foreach ($response->json() ?? [] as $coin) {
                $id = $coin['id'] ?? null;
                if (! $id || ! is_numeric($coin['current_price'] ?? null)) {
                    continue;
                }

                $result[$id] = [
                    'price' => (float) $coin['current_price'],
                    'market_cap' => (float) ($coin['market_cap'] ?? 0),
                    'volume_24h' => (float) ($coin['total_volume'] ?? 0),
                    'change_24h' => (float) ($coin['price_change_percentage_24h'] ?? 0),
                ];
            }

            return $result;
        } catch (\Throwable $e) {
            Log::warning('Coin price feed unavailable: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Live USD price for a tracked coin, falling back to the configured rate.
     */
    public function usd(string $coin): float
    {
        $live = $this->livePrices()[$coin] ?? null;

        if (is_numeric($live) && (float) $live > 0) {
            return (float) $live;
        }

        $config = self::COINS[$coin] ?? null;
        if (! $config) {
            return 0.0;
        }

        return (float) PlatformSetting::get($config['setting'], $config['default']);
    }

    /**
     * @return array<string, float>
     */
    protected function livePrices(): array
    {
        if (! config('services.crypto_prices.enabled')) {
            return [];
        }

        return Cache::remember(self::CACHE_KEY, (int) config('services.crypto_prices.ttl', 60), function () {
            try {
                $response = Http::timeout((int) config('services.crypto_prices.timeout', 5))
                    ->retry(2, 200)
                    ->get(config('services.crypto_prices.url'), [
                        'ids' => implode(',', array_keys(self::COINS)),
                        'vs_currencies' => 'usd',
                    ]);

                if (! $response->successful()) {
                    Log::warning('Crypto price feed returned HTTP '.$response->status());

                    return [];
                }

                $prices = [];
                foreach (array_keys(self::COINS) as $coin) {
                    $usd = $response->json("{$coin}.usd");
                    if (is_numeric($usd) && (float) $usd > 0) {
                        $prices[$coin] = (float) $usd;
                    }
                }

                return $prices;
            } catch (\Throwable $e) {
                Log::warning('Crypto price feed unavailable: '.$e->getMessage());

                return [];
            }
        });
    }
}
