<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'deposit_method_id',
        'currency',
        'amount',
        'txid',
        'status', // pending, confirmed, rejected
        'admin_notes',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function depositMethod(): BelongsTo
    {
        return $this->belongsTo(DepositMethod::class);
    }

    public function getShortTxidAttribute(): string
    {
        if (strlen($this->txid) > 14) {
            return substr($this->txid, 0, 6).'...'.substr($this->txid, -6);
        }

        return $this->txid;
    }
}
