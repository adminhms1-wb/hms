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
        Schema::create('housekeeping_tasks', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign keys
            $table->foreignId('room_id')
                  ->constrained('rooms')
                  ->onDelete('cascade');

            // staff table assumed (can be users with role = staff)
            $table->foreignId('staff_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Task details
            $table->string('task_type'); // cleaning, inspection, linen_change, etc.

            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('pending');

            $table->date('date'); // Scheduled date

            $table->timestamps();
            $table->softDeletes(); // Optional
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housekeeping_tasks');
    }
};
