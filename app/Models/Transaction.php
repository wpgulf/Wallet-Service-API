<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    public $timestamps = false;

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'related_wallet_id',
        'idempotency_key',
        'balance_after',
        'created_at',
    ];

    protected $casts = [
        'wallet_id' => 'integer',
        'amount' => 'integer',
        'related_wallet_id' => 'integer',
        'balance_after' => 'integer',
    ];
}
