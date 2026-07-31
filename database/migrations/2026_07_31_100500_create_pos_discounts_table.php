<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outlet_id');
            $table->string('name', 100);
            $table->string('type', 20);
            $table->decimal('value', 15, 2);
            $table->decimal('min_purchase', 15, 2)->nullable();
            $table->boolean('member_only')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('priority')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('conditions')->nullable();
            $table->timestamps();

            $table->index('outlet_id');
            $table->index('is_active');
            $table->index('starts_at');
            $table->index('ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_discounts');
    }
};
