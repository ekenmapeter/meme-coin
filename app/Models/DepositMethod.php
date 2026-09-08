<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepositMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency',
        'name',
        'network',
        'wallet_address',
        'qr_code_url',
        'min_deposit',
        'confirmations_required',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'confirmations_required' => 'integer',
    ];

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }
}
