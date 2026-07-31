<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_discounts', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('conditions');
            $table->index('product_id');
        });

        Schema::table('pos_vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('is_active');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('pos_discounts', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropColumn('product_id');
        });

        Schema::table('pos_vouchers', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};
