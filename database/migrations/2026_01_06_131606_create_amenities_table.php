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
        Schema::create('amenities', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('name'); // Amenity name, e.g., WiFi, Breakfast
            $table->boolean('is_paid')->default(false); // Whether it's a paid amenity
            $table->decimal('price', 10, 2)->default(0.00); // Price if paid
            $table->timestamps();
            $table->softDeletes(); // Optional soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amenities');
    }
};
