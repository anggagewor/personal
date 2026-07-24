<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('position');
            $table->string('recurrence_type')->nullable()->after('is_recurring'); // daily, weekly, monthly
            $table->date('recurrence_until')->nullable()->after('recurrence_type');
            $table->timestamp('last_recurred_at')->nullable()->after('recurrence_until');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['is_recurring', 'recurrence_type', 'recurrence_until', 'last_recurred_at']);
        });
    }
};
