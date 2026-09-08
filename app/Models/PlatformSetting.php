<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    use HasFactory;

    /**
     * Per-request memoized values so repeated get() calls hit the DB once.
     *
     * @var array<string, string|null>|null
     */
    protected static ?array $memoized = null;

    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, $default = null)
    {
        if (static::$memoized === null) {
            static::$memoized = static::pluck('value', 'key')->all();
        }

        return static::$memoized[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        // Bust the memoized cache so subsequent reads see the new value.
        static::$memoized = null;
    }
}
