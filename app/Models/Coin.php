<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coin extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ticker',
        'description',
        'logo_path',
        'contract_address',
        'network',
        'current_price',
        'initial_price',
        'market_cap',
        'change_24h',
        'volume_24h',
        'holders_count',
        'buyers_count',
        'liquidity',
        'total_supply',
        'is_featured',
        'is_trending',
        'is_active',
        'price_movement_mode',
        'auto_step_percent',
        'auto_interval_seconds',
        'last_auto_tick_at',
    ];

    protected $casts = [
        'current_price' => 'float',
        'initial_price' => 'float',
        'market_cap' => 'float',
        'change_24h' => 'float',
        'volume_24h' => 'float',
        'holders_count' => 'integer',
        'buyers_count' => 'integer',
        'liquidity' => 'float',
        'total_supply' => 'float',
        'is_featured' => 'boolean',
        'is_trending' => 'boolean',
        'is_active' => 'boolean',
        'auto_step_percent' => 'float',
        'auto_interval_seconds' => 'integer',
        'last_auto_tick_at' => 'datetime',
    ];

    public function holdings(): HasMany
    {
        return $this->hasMany(UserHolding::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(CoinTrade::class)->latest();
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class)->orderBy('candle_time');
    }

    public function swaps(): HasMany
    {
        return $this->hasMany(Swap::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->current_price < 0.0001) {
            return '$'.rtrim(sprintf('%.8f', $this->current_price), '0');
        } elseif ($this->current_price < 1) {
            return '$'.sprintf('%.6f', $this->current_price);
        } else {
            return '$'.number_format($this->current_price, 4);
        }
    }

    /**
     * Logo URL with a cache-busting version derived from the file's mtime.
     * Guarantees replaced logos are never served from a stale browser cache
     * (relevant when running under `php artisan serve`, which ignores .htaccess).
     */
    public function getLogoUrlAttribute(): string
    {
        $path = 'images/coins/'.$this->logo_path;
        $version = @filemtime(public_path($path)) ?: 1;

        return asset($path).'?v='.$version;
    }

    public function getFormattedMarketCapAttribute(): string
    {
        return self::formatNumberAbbreviated($this->market_cap);
    }

    public function getFormattedVolumeAttribute(): string
    {
        return self::formatNumberAbbreviated($this->volume_24h);
    }

    public function getFormattedHoldersAttribute(): string
    {
        return self::formatNumberAbbreviated($this->holders_count, false);
    }

    public static function formatNumberAbbreviated($number, $withDollar = true): string
    {
        $prefix = $withDollar ? '$' : '';
        if ($number >= 1000000000) {
            return $prefix.number_format($number / 1000000000, 2).'B';
        }
        if ($number >= 1000000) {
            return $prefix.number_format($number / 1000000, 2).'M';
        }
        if ($number >= 1000) {
            return $prefix.number_format($number / 1000, 1).'K';
        }

        return $prefix.number_format($number);
    }
}
