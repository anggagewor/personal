<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('converter_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->string('icon', 50)->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::create('converter_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name', 100);
            $table->string('symbol', 20);
            $table->double('to_base');
            $table->boolean('is_base')->default(false);
            $table->timestamps();

            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('converter_units');
        Schema::dropIfExists('converter_categories');
    }
};
