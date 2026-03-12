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
        Schema::table('attendance', function (Blueprint $table) {
            // Add shift_schedule_id if it doesn't exist
            if (!Schema::hasColumn('attendance', 'shift_schedule_id')) {
                $table->foreignId('shift_schedule_id')
                      ->nullable()
                      ->after('staff_id')
                      ->constrained('shift_schedules')
                      ->onDelete('set null');
            }

            // Add attendance_date if it doesn't exist (keep existing 'date' column for backward compatibility)
            if (!Schema::hasColumn('attendance', 'attendance_date')) {
                $table->date('attendance_date')->nullable()->after('shift_schedule_id');
            }

            // Add check_in_time if it doesn't exist
            if (!Schema::hasColumn('attendance', 'check_in_time')) {
                $table->time('check_in_time')->nullable()->after('attendance_date');
            }

            // Add check_out_time if it doesn't exist
            if (!Schema::hasColumn('attendance', 'check_out_time')) {
                $table->time('check_out_time')->nullable()->after('check_in_time');
            }

            // Add check_in_type if it doesn't exist
            if (!Schema::hasColumn('attendance', 'check_in_type')) {
                $table->enum('check_in_type', ['manual', 'biometric'])->nullable()->after('check_out_time');
            }

            // Add notes if it doesn't exist
            if (!Schema::hasColumn('attendance', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }

            // Update status enum to include new values if needed
            // Note: Laravel doesn't support modifying enum easily, so we'll keep the existing enum
            // and handle the new values in the application layer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            if (Schema::hasColumn('attendance', 'shift_schedule_id')) {
                $table->dropForeign(['shift_schedule_id']);
                $table->dropColumn('shift_schedule_id');
            }
            if (Schema::hasColumn('attendance', 'attendance_date')) {
                $table->dropColumn('attendance_date');
            }
            if (Schema::hasColumn('attendance', 'check_in_time')) {
                $table->dropColumn('check_in_time');
            }
            if (Schema::hasColumn('attendance', 'check_out_time')) {
                $table->dropColumn('check_out_time');
            }
            if (Schema::hasColumn('attendance', 'check_in_type')) {
                $table->dropColumn('check_in_type');
            }
            if (Schema::hasColumn('attendance', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
