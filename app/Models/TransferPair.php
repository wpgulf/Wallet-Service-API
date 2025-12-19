<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferPair extends Model
{
    protected $table = 'transfer_pairs';

    public $timestamps = false;

    protected $fillable = [
        'debit_transaction_id',
        'credit_transaction_id',
        'idempotency_key',
        'created_at',
    ];

    protected $casts = [
        'debit_transaction_id' => 'integer',
        'credit_transaction_id' => 'integer',
    ];
}
