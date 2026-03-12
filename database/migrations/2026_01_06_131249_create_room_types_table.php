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
        Schema::create('room_types', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('hotel_id'); // FK to hotels
            $table->string('name'); // Room type name, e.g., Deluxe, Suite
            $table->decimal('base_price', 10, 2)->default(0.00); // Base price per night
            $table->integer('max_guests')->default(1); // Max guests allowed
            $table->timestamps();
            $table->softDeletes(); // Optional, for soft deletion
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
