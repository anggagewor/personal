<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outlet_id');
            $table->unsignedBigInteger('supplier_id');
            $table->string('po_number', 30);
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->string('status', 20)->default('draft');
            $table->string('payment_status', 20)->default('unpaid');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('outlet_id');
            $table->index('supplier_id');
            $table->index('status');
            $table->index('payment_status');
            $table->unique('po_number');
            $table->index('order_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_purchase_orders');
    }
};
