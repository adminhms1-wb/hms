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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign keys
            $table->foreignId('hotel_id');
            $table->foreignId('room_type_id');

            // Room details
            $table->string('room_number'); // Room number, e.g., 101
            $table->string('floor')->nullable(); // Floor name/number
            $table->string('bed_type')->nullable(); // Bed type, e.g., King, Twin
            $table->boolean('smoking')->default(false); // Smoking allowed?
            
            // Room status
            $table->enum('status', ['available', 'reserved', 'checked_in', 'checked_out', 'maintenance'])
                  ->default('available');

            $table->timestamps();
            $table->softDeletes(); // Optional soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
