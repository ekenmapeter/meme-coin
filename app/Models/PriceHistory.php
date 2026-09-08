<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'coin_id',
        'timeframe',
        'open',
        'high',
        'low',
        'close',
        'volume',
        'candle_time',
    ];

    protected $casts = [
        'open' => 'float',
        'high' => 'float',
        'low' => 'float',
        'close' => 'float',
        'volume' => 'float',
        'candle_time' => 'datetime',
    ];

    public function coin(): BelongsTo
    {
        return $this->belongsTo(Coin::class);
    }
}
