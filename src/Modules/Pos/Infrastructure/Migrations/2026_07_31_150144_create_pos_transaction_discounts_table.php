<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_transaction_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->string('name', 150);
            $table->string('type', 20);
            $table->decimal('value', 15, 2);
            $table->decimal('discount_amount', 15, 2);
            $table->timestamp('created_at')->useCurrent();

            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_transaction_discounts');
    }
};
