<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_watchlist_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('symbol', 20);         // e.g. USD/IDR, BTC/USD, AAPL
            $table->string('type', 20);           // forex, crypto, stock, commodity
            $table->string('label', 50)->nullable(); // custom display name
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'symbol']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_watchlist_items');
    }
};
