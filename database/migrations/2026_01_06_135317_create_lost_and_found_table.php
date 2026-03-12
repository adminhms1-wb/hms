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
         Schema::create('lost_and_found', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign key to room
            $table->foreignId('room_id')
                  ->nullable()
                  ->constrained('rooms')
                  ->nullOnDelete();

            $table->string('item'); // Item description
            $table->date('found_date'); // Date item was found

            $table->enum('status', [
                'found',
                'claimed',
                'unclaimed',
                'discarded'
            ])->default('found');

            $table->timestamps();
            $table->softDeletes(); // Optional
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lost_and_found');
    }
};
