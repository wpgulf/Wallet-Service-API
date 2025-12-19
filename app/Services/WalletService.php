<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransferPair;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public function createWallet(string $ownerName, string $currency): Wallet
    {
        return Wallet::create([
            'owner_name' => $ownerName,
            'currency' => strtoupper($currency),
            'balance' => 0,
        ]);
    }

    public function listWallets(?string $ownerName, ?string $currency)
    {
        $q = Wallet::query();

        if ($ownerName) $q->where('owner_name', $ownerName);
        if ($currency) $q->where('currency', strtoupper($currency));

        return $q->orderByDesc('id')->get();
    }

    public function getWallet(int $id): Wallet
    {
        return Wallet::query()->findOrFail($id);
    }

    public function getBalance(int $id): array
    {
        $w = $this->getWallet($id);
        return ['wallet_id' => $w->id, 'currency' => $w->currency, 'balance' => (int)$w->balance];
    }

    /**
     * Pure PHP style idempotency:
     * - for deposit/withdraw: reuse idempotency_key in transactions (unique)
     * - if same key exists, return same result
     */
    public function deposit(int $walletId, int $amount, string $idempotencyKey): array
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Amount must be > 0']);
        }

        // If already processed, return existing result
        if ($idempotencyKey !== '') {
            $existing = Transaction::query()
                ->where('idempotency_key', $idempotencyKey)
                ->where('wallet_id', $walletId)
                ->where('type', 'deposit')
                ->first();
            if ($existing) {
                return ['wallet_id' => $walletId, 'balance' => (int)$existing->balance_after];
            }
        }

        return DB::transaction(function () use ($walletId, $amount, $idempotencyKey) {
            $w = Wallet::query()->whereKey($walletId)->lockForUpdate()->firstOrFail();

            $w->balance += $amount;
            $w->save();

            Transaction::create([
                'wallet_id' => $w->id,
                'type' => 'deposit',
                'amount' => $amount,
                'related_wallet_id' => null,
                'idempotency_key' => $idempotencyKey ?: null,
                'balance_after' => $w->balance,
                'created_at' => now(),
            ]);

            return ['wallet_id' => $w->id, 'balance' => (int)$w->balance];
        });
    }

    public function withdraw(int $walletId, int $amount, string $idempotencyKey): array
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Amount must be > 0']);
        }

        if ($idempotencyKey !== '') {
            $existing = Transaction::query()
                ->where('idempotency_key', $idempotencyKey)
                ->where('wallet_id', $walletId)
                ->where('type', 'withdrawal')
                ->first();
            if ($existing) {
                return ['wallet_id' => $walletId, 'balance' => (int)$existing->balance_after];
            }
        }

        return DB::transaction(function () use ($walletId, $amount, $idempotencyKey) {
            $w = Wallet::query()->whereKey($walletId)->lockForUpdate()->firstOrFail();

            if ($w->balance < $amount) {
                throw ValidationException::withMessages(['balance' => 'Insufficient funds']);
            }

            $w->balance -= $amount;
            $w->save();

            Transaction::create([
                'wallet_id' => $w->id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'related_wallet_id' => null,
                'idempotency_key' => $idempotencyKey ?: null,
                'balance_after' => $w->balance,
                'created_at' => now(),
            ]);

            return ['wallet_id' => $w->id, 'balance' => (int)$w->balance];
        });
    }

    /**
     * Transfer Pure PHP style:
     * - uses transfer_pairs unique idempotency_key
     * - creates debit + credit transactions and links them
     */
    public function transfer(int $fromId, int $toId, int $amount, string $idempotencyKey): array
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Amount must be > 0']);
        }
        if ($fromId === $toId) {
            throw ValidationException::withMessages(['to_wallet_id' => 'Self-transfer not allowed']);
        }
        if ($idempotencyKey === '') {
            throw ValidationException::withMessages(['idempotency_key' => 'Idempotency-Key header is required for transfers']);
        }

        // If transfer already processed, return balances from the linked transactions
        $pair = TransferPair::query()->where('idempotency_key', $idempotencyKey)->first();
        if ($pair) {
            $debit = Transaction::query()->find($pair->debit_transaction_id);
            $credit = Transaction::query()->find($pair->credit_transaction_id);

            return [
                'from_wallet_id' => $fromId,
                'to_wallet_id' => $toId,
                'amount' => $amount,
                'from_balance' => (int)($debit?->balance_after ?? 0),
                'to_balance' => (int)($credit?->balance_after ?? 0),
            ];
        }

        return DB::transaction(function () use ($fromId, $toId, $amount, $idempotencyKey) {

            // Lock in deterministic order to reduce deadlocks
            $a = min($fromId, $toId);
            $b = max($fromId, $toId);

            $wa = Wallet::query()->whereKey($a)->lockForUpdate()->firstOrFail();
            $wb = Wallet::query()->whereKey($b)->lockForUpdate()->firstOrFail();

            $from = ($fromId === $a) ? $wa : $wb;
            $to   = ($toId === $a) ? $wa : $wb;

            if ($from->currency !== $to->currency) {
                throw ValidationException::withMessages(['currency' => 'Currency mismatch']);
            }
            if ($from->balance < $amount) {
                throw ValidationException::withMessages(['balance' => 'Insufficient funds']);
            }

            // debit
            $from->balance -= $amount;
            $from->save();

            $debitTx = Transaction::create([
                'wallet_id' => $from->id,
                'type' => 'transfer_debit',
                'amount' => $amount,
                'related_wallet_id' => $to->id,
                'idempotency_key' => null,
                'balance_after' => $from->balance,
                'created_at' => now(),
            ]);

            // credit
            $to->balance += $amount;
            $to->save();

            $creditTx = Transaction::create([
                'wallet_id' => $to->id,
                'type' => 'transfer_credit',
                'amount' => $amount,
                'related_wallet_id' => $from->id,
                'idempotency_key' => null,
                'balance_after' => $to->balance,
                'created_at' => now(),
            ]);

            // link pair (unique key enforces idempotency)
            TransferPair::create([
                'debit_transaction_id' => $debitTx->id,
                'credit_transaction_id' => $creditTx->id,
                'idempotency_key' => $idempotencyKey,
                'created_at' => now(),
            ]);

            return [
                'from_wallet_id' => $from->id,
                'to_wallet_id' => $to->id,
                'amount' => $amount,
                'from_balance' => (int)$from->balance,
                'to_balance' => (int)$to->balance,
            ];
        });
    }

    public function listTransactions(int $walletId, array $filters)
    {
        $q = Transaction::query()->where('wallet_id', $walletId);

        if (!empty($filters['type'])) $q->where('type', $filters['type']);
        if (!empty($filters['from'])) $q->where('created_at', '>=', $filters['from']);
        if (!empty($filters['to'])) $q->where('created_at', '<=', $filters['to']);

        $perPage = min(100, max(1, (int)($filters['per_page'] ?? 20)));

        return $q->orderByDesc('created_at')->orderByDesc('id')->paginate($perPage);
    }
}
