<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_price_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('symbol', 20);
            $table->decimal('price', 16, 6);
            $table->decimal('change', 12, 6)->default(0);        // absolute change
            $table->decimal('change_percent', 8, 4)->default(0); // percent change
            $table->decimal('previous_close', 16, 6)->nullable();
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->index(['user_id', 'symbol', 'fetched_at']);
            $table->index('fetched_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_price_history');
    }
};
