<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outlet_id');
            $table->unsignedBigInteger('category_id');
            $table->string('name', 150);
            $table->decimal('base_price', 15, 2);
            $table->string('sku', 50)->nullable();
            $table->string('image', 255)->nullable();
            $table->boolean('has_variants')->default(false);
            $table->boolean('track_stock')->default(true);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index('outlet_id');
            $table->index('category_id');
            $table->index('status');
            $table->unique(['outlet_id', 'category_id', 'name']);
            $table->index('sku');
        });

        Schema::create('pos_product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('name', 100);
            $table->string('sku', 50)->nullable();
            $table->decimal('price', 15, 2);
            $table->integer('stock_quantity')->default(0);
            $table->timestamps();

            $table->index('product_id');
            $table->index('sku');
        });

        Schema::create('pos_stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_variant_id');
            $table->string('type', 20);
            $table->integer('quantity');
            $table->string('reason', 255)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('product_variant_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_stock_adjustments');
        Schema::dropIfExists('pos_product_variants');
        Schema::dropIfExists('pos_products');
    }
};
