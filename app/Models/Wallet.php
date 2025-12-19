<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallets';

    protected $fillable = ['owner_name', 'currency', 'balance'];

    protected $casts = [
        'balance' => 'integer',
    ];
}
