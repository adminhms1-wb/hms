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
       Schema::create('service_bookings', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign keys
            $table->foreignId('service_id')
                  ->constrained('services')
                  ->onDelete('cascade');

            $table->foreignId('guest_id')
                  ->constrained('guests')
                  ->onDelete('cascade');

            $table->foreignId('room_id')
                  ->nullable()
                  ->constrained('rooms')
                  ->nullOnDelete();

            // Booking details
            $table->date('date'); // Service date

            $table->enum('status', [
                'pending',
                'confirmed',
                'completed',
                'cancelled'
            ])->default('pending');

            $table->timestamps();
            $table->softDeletes(); // Optional
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_bookings');
    }
};
