<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('code', 10);
            $table->string('name', 100);
            $table->string('type', 20);
            $table->string('normal_balance', 10);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->tinyInteger('depth')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'code']);
            $table->index(['user_id', 'type']);
            $table->index('parent_id');
        });

        Schema::create('accounting_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('entry_number');
            $table->date('date');
            $table->string('description', 255);
            $table->decimal('total_debit', 15, 2);
            $table->timestamps();

            $table->unique(['user_id', 'entry_number']);
            $table->index(['user_id', 'date']);
        });

        Schema::create('accounting_journal_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_entry_id');
            $table->unsignedBigInteger('account_id');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);

            $table->index('journal_entry_id');
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_journal_lines');
        Schema::dropIfExists('accounting_journal_entries');
        Schema::dropIfExists('accounting_accounts');
    }
};
