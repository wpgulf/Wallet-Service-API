<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED
            $table->string('owner_name', 255);
            $table->char('currency', 3);
            $table->bigInteger('balance')->default(0);

            $table->timestamps(); // created_at, updated_at

            $table->index('owner_name', 'idx_owner_name');
            $table->index('currency', 'idx_currency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
