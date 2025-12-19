<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function store(Request $request, WalletService $svc)
    {
        $data = $request->validate([
            'from_wallet_id' => 'required|integer|min:1',
            'to_wallet_id' => 'required|integer|min:1|different:from_wallet_id',
            'amount' => 'required|integer|min:1',
        ]);

        $key = (string)$request->header('Idempotency-Key', '');

        return $svc->transfer(
            (int)$data['from_wallet_id'],
            (int)$data['to_wallet_id'],
            (int)$data['amount'],
            $key
        );
    }
}
