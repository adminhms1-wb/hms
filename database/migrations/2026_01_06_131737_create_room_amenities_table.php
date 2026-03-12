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
        Schema::create('room_amenities', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign keys
            $table->foreignId('room_id');
            $table->foreignId('amenity_id');

            $table->timestamps();

            // Prevent duplicate room-amenity pairs
            $table->unique(['room_id', 'amenity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_amenities');
    }
};
