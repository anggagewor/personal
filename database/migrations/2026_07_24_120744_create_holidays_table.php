<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('summary');
            $table->string('description')->nullable();
            $table->boolean('is_national_holiday')->default(true);
            $table->timestamps();

            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
