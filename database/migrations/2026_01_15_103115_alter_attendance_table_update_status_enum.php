<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the status enum to include new values
        // MySQL requires raw SQL to modify enum columns
        DB::statement("ALTER TABLE `attendance` MODIFY COLUMN `status` ENUM('present', 'absent', 'late', 'leave', 'half_day', 'on_leave') DEFAULT 'present'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        // First, update any records with new status values to old values
        DB::statement("UPDATE `attendance` SET `status` = 'leave' WHERE `status` IN ('half_day', 'on_leave')");
        
        // Then modify the enum back to original values
        DB::statement("ALTER TABLE `attendance` MODIFY COLUMN `status` ENUM('present', 'absent', 'leave', 'late') DEFAULT 'present'");
    }
};
