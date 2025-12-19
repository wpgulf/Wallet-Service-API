<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transfer_pairs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('debit_transaction_id')
                ->constrained('transactions')
                ->cascadeOnDelete();

            $table->foreignId('credit_transaction_id')
                ->constrained('transactions')
                ->cascadeOnDelete();

            $table->string('idempotency_key', 255);
            $table->timestamp('created_at')->useCurrent();

            $table->unique('idempotency_key', 'unique_transfer_idempotency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_pairs');
    }
};
