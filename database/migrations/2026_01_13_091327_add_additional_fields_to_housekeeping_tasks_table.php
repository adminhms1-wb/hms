<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('housekeeping_tasks', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('date');
            $table->timestamp('started_at')->nullable()->after('notes');
            $table->timestamp('completed_at')->nullable()->after('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('housekeeping_tasks', function (Blueprint $table) {
            $table->dropColumn(['notes', 'started_at', 'completed_at']);
        });
    }
};
