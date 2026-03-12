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
        Schema::create('checkins', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign key to reservation
            $table->foreignId('reservation_id');

            // Check-in details
            $table->timestamp('checkin_time')->nullable(); // Check-in datetime
            $table->decimal('deposit', 10, 2)->default(0.00); // Deposit paid

            $table->timestamps();
            $table->softDeletes(); // Optional soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkins');
    }
};
