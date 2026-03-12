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
        Schema::create('attendance', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign key to staff
            $table->foreignId('staff_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Attendance details
            $table->date('date'); // Attendance date
            $table->enum('status', [
                'present',
                'absent',
                'leave',
                'late'
            ])->default('present');

            $table->timestamps();
            $table->softDeletes(); // Optional
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
