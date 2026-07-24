<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('name');
            $table->string('color')->nullable();
            $table->timestamps();
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tag_id')->index();
            $table->unsignedBigInteger('taggable_id');
            $table->string('taggable_type');
            $table->timestamps();

            $table->index(['taggable_id', 'taggable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
    }
};
