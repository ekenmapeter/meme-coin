<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Swap extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coin_id',
        'token_amount',
        'usd_value',
        'btc_amount_gross',
        'fee_percent',
        'fee_btc',
        'net_btc_received',
        'rate',
        'status',
    ];

    protected $casts = [
        'token_amount' => 'float',
        'usd_value' => 'float',
        'btc_amount_gross' => 'float',
        'fee_percent' => 'float',
        'fee_btc' => 'float',
        'net_btc_received' => 'float',
        'rate' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coin(): BelongsTo
    {
        return $this->belongsTo(Coin::class);
    }
}
