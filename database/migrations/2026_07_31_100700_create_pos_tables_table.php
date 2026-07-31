<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outlet_id');
            $table->string('name', 50);
            $table->string('token', 64);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('outlet_id');
            $table->unique('token');
        });

        Schema::create('pos_table_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('table_id');
            $table->string('status', 20)->default('active');
            $table->timestamp('started_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('table_id');
            $table->index('status');
        });

        Schema::create('pos_order_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('table_session_id');
            $table->json('items');
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('table_session_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_order_queue');
        Schema::dropIfExists('pos_table_sessions');
        Schema::dropIfExists('pos_tables');
    }
};
