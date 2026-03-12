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
        Schema::create('service_time_slots', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to service
            $table->foreignId('service_id')
                  ->constrained('services')
                  ->onDelete('cascade');
            
            // Time slot details
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_minutes')->nullable(); // Duration in minutes
            
            // Availability
            $table->boolean('is_available')->default(true);
            $table->integer('max_bookings')->default(1); // Max concurrent bookings for this slot
            
            // Days of week (0 = Sunday, 6 = Saturday)
            $table->json('available_days')->nullable(); // [0,1,2,3,4,5,6] for days available
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_time_slots');
    }
};
