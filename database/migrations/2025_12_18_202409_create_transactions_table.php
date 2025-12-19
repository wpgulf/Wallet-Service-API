<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED

            $table->foreignId('wallet_id')
                ->constrained('wallets')
                ->cascadeOnDelete();

            $table->enum('type', ['deposit', 'withdrawal', 'transfer_debit', 'transfer_credit']);

            $table->bigInteger('amount');

            $table->foreignId('related_wallet_id')
                ->nullable()
                ->constrained('wallets')
                ->nullOnDelete();

            $table->string('idempotency_key', 255)->nullable();

            $table->bigInteger('balance_after');

            $table->timestamp('created_at')->useCurrent();

            // Indexes + Unique (match Pure PHP)
            $table->unique('idempotency_key', 'unique_idempotency');
            $table->index('wallet_id', 'idx_wallet_id');
            $table->index('type', 'idx_type');
            $table->index('created_at', 'idx_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
