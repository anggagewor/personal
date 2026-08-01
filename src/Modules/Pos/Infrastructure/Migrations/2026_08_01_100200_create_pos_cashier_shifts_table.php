<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_cashier_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outlet_id');
            $table->unsignedBigInteger('user_id');
            $table->string('cashier_name', 100);
            $table->decimal('opening_amount', 15, 2)->default(0);
            $table->decimal('closing_amount', 15, 2)->nullable();
            $table->decimal('expected_amount', 15, 2)->nullable()->comment('Calculated: opening + cash sales - cash refunds');
            $table->decimal('difference', 15, 2)->nullable()->comment('closing - expected');
            $table->string('status', 20)->default('open'); // open, closed
            $table->text('notes')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('outlet_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('opened_at');
        });

        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('shift_id')->nullable()->after('table_session_id');
            $table->index('shift_id');
        });
    }

    public function down(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->dropIndex(['shift_id']);
            $table->dropColumn('shift_id');
        });

        Schema::dropIfExists('pos_cashier_shifts');
    }
};
