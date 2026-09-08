<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'currency',
        'amount',
        'network_fee',
        'net_amount',
        'destination_address',
        'status', // pending, approved, completed, rejected
        'txid',
        'admin_notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'network_fee' => 'float',
        'net_amount' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getShortAddressAttribute(): string
    {
        if (strlen($this->destination_address) > 14) {
            return substr($this->destination_address, 0, 6).'...'.substr($this->destination_address, -6);
        }

        return $this->destination_address;
    }
}
