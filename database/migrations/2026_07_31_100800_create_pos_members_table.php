<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outlet_id');
            $table->string('name', 100);
            $table->string('phone', 20);
            $table->string('email', 100)->nullable();
            $table->timestamps();

            $table->index('outlet_id');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_members');
    }
};
