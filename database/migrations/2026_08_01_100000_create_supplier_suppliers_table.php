<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outlet_id');
            $table->string('name', 100);
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('bank_name', 50)->nullable();
            $table->string('bank_account_number', 30)->nullable();
            $table->string('bank_account_holder', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('outlet_id');
            $table->index(['outlet_id', 'name']);
            $table->index(['outlet_id', 'phone']);
            $table->index(['outlet_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_suppliers');
    }
};
