<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoinTrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'coin_id',
        'user_id',
        'wallet_address',
        'type', // 'buy' or 'sell'
        'token_amount',
        'usd_amount',
        'price',
    ];

    protected $casts = [
        'token_amount' => 'float',
        'usd_amount' => 'float',
        'price' => 'float',
    ];

    public function coin(): BelongsTo
    {
        return $this->belongsTo(Coin::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getShortWalletAttribute(): string
    {
        if (strlen($this->wallet_address) > 10) {
            return substr($this->wallet_address, 0, 4).'...'.substr($this->wallet_address, -4);
        }

        return $this->wallet_address;
    }

    public function getFormattedTokenAmountAttribute(): string
    {
        return Coin::formatNumberAbbreviated($this->token_amount, false);
    }
}
