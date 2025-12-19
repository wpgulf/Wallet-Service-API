<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index(Request $request, WalletService $svc)
    {
        return $svc->listWallets(
            $request->get('owner_name'),
            $request->get('currency')
        );
    }

    public function store(Request $request, WalletService $svc)
    {
        $data = $request->validate([
            'owner_name' => 'required|string|max:255',
            'currency' => 'required|string|size:3',
        ]);

        return response()->json($svc->createWallet($data['owner_name'], $data['currency']), 201);
    }

    public function show(int $id, WalletService $svc)
    {
        return $svc->getWallet($id);
    }

    public function balance(int $id, WalletService $svc)
    {
        return $svc->getBalance($id);
    }

    public function transactions(int $id, Request $request, WalletService $svc)
    {
        $filters = [
            'type' => $request->get('type'),
            'from' => $request->get('from'),
            'to' => $request->get('to'),
            'page' => (int)$request->get('page', 1),
            'per_page' => (int)$request->get('per_page', 20),
        ];

        return $svc->listTransactions($id, $filters);
    }

    public function deposit(int $id, Request $request, WalletService $svc)
    {
        $data = $request->validate(['amount' => 'required|integer|min:1']);
        $key = (string)$request->header('Idempotency-Key', '');

        return $svc->deposit($id, (int)$data['amount'], $key);
    }

    public function withdraw(int $id, Request $request, WalletService $svc)
    {
        $data = $request->validate(['amount' => 'required|integer|min:1']);
        $key = (string)$request->header('Idempotency-Key', '');

        return $svc->withdraw($id, (int)$data['amount'], $key);
    }
}
