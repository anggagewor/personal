<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outlet_id');
            $table->string('transaction_number', 30);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_method_type', 20)->nullable();
            $table->decimal('amount_tendered', 15, 2)->nullable();
            $table->decimal('change_amount', 15, 2)->nullable();
            $table->string('status', 20)->default('completed');
            $table->string('void_reason', 255)->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->unsignedBigInteger('table_session_id')->nullable();
            $table->string('voucher_code', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('transaction_number');
            $table->index('outlet_id');
            $table->index('status');
            $table->index('member_id');
            $table->index('created_at');
        });

        Schema::create('pos_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->string('product_name', 150);
            $table->string('variant_name', 100)->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamp('created_at')->nullable();

            $table->index('transaction_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_transaction_items');
        Schema::dropIfExists('pos_transactions');
    }
};
