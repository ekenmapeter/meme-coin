<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserHolding extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coin_id',
        'token_balance',
        'avg_buy_price',
    ];

    protected $casts = [
        'token_balance' => 'float',
        'avg_buy_price' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coin(): BelongsTo
    {
        return $this->belongsTo(Coin::class);
    }

    public function getCurrentValueUsdAttribute(): float
    {
        return $this->token_balance * ($this->coin->current_price ?? 0);
    }
}
