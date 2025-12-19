<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\TransferController;

Route::get('/health', fn () => ['status' => 'ok']);

Route::post('/wallets', [WalletController::class, 'store']);
Route::get('/wallets', [WalletController::class, 'index']);

Route::get('/wallets/{id}', [WalletController::class, 'show']);
Route::get('/wallets/{id}/balance', [WalletController::class, 'balance']);
Route::get('/wallets/{id}/transactions', [WalletController::class, 'transactions']);

Route::post('/wallets/{id}/deposit', [WalletController::class, 'deposit']);
Route::post('/wallets/{id}/withdraw', [WalletController::class, 'withdraw']);

Route::post('/transfers', [TransferController::class, 'store']);
