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
        // Check if shifts table exists and doesn't have name column
        if (Schema::hasTable('shifts') && !Schema::hasColumn('shifts', 'name')) {
            Schema::table('shifts', function (Blueprint $table) {
                // If it has staff_id, we need to handle it differently
                if (Schema::hasColumn('shifts', 'staff_id')) {
                    // This is the old structure - we'll create a new table for shift templates
                    // But for now, let's just add name and description if possible
                    $table->string('name')->nullable()->after('id');
                    $table->text('description')->nullable()->after('end_time');
                    $table->boolean('is_active')->default(true)->after('description');
                } else {
                    // New structure - just add missing columns
                    $table->string('name')->after('id');
                    $table->text('description')->nullable()->after('end_time');
                    $table->boolean('is_active')->default(true)->after('description');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('shifts')) {
            Schema::table('shifts', function (Blueprint $table) {
                if (Schema::hasColumn('shifts', 'name')) {
                    $table->dropColumn('name');
                }
                if (Schema::hasColumn('shifts', 'description')) {
                    $table->dropColumn('description');
                }
                if (Schema::hasColumn('shifts', 'is_active')) {
                    $table->dropColumn('is_active');
                }
            });
        }
    }
};
