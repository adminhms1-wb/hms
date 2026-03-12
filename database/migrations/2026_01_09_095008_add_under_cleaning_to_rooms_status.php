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
        // Update enum to include 'under_cleaning'
        DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available', 'reserved', 'checked_in', 'checked_out', 'under_cleaning', 'maintenance') DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum
        DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available', 'reserved', 'checked_in', 'checked_out', 'maintenance') DEFAULT 'available'");
    }
};
