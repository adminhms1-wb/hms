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
        Schema::create('seasonal_prices', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign key
            $table->foreignId('room_type_id');

            // Date range
            $table->date('start_date');
            $table->date('end_date');

            // Price for the season
            $table->decimal('price', 10, 2)->default(0.00);

            $table->timestamps();
            $table->softDeletes(); // Optional soft deletes

            // Optional: prevent overlapping for same room_type (enforce unique date ranges)
            // $table->unique(['room_type_id', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasonal_prices');
    }
};
