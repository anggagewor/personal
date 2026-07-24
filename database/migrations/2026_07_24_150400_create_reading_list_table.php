<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_list', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('title');
            $table->string('url', 2048);
            $table->string('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('domain')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_favorite')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_list');
    }
};
