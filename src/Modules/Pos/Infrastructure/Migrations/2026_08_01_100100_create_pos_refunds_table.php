<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->string('refund_number', 30)->unique();
            $table->decimal('refund_amount', 15, 2);
            $table->string('reason', 500);
            $table->string('refund_method', 50)->nullable()->comment('cash, original_method, store_credit');
            $table->timestamps();

            $table->index('transaction_id');
        });

        Schema::create('pos_refund_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('refund_id');
            $table->unsignedBigInteger('transaction_item_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->string('product_name', 150);
            $table->string('variant_name', 100)->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('refund_amount', 15, 2);
            $table->timestamp('created_at')->nullable();

            $table->index('refund_id');
            $table->index('transaction_item_id');
        });

        // Add refund tracking fields to pos_transactions
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->decimal('refunded_amount', 15, 2)->default(0)->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->dropColumn('refunded_amount');
        });

        Schema::dropIfExists('pos_refund_items');
        Schema::dropIfExists('pos_refunds');
    }
};
