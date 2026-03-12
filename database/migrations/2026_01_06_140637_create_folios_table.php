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
        Schema::create('folios', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign key to reservation
            $table->foreignId('reservation_id');

            $table->decimal('total', 10, 2)->default(0.00);   // Total bill amount
            $table->decimal('paid', 10, 2)->default(0.00);    // Amount paid
            $table->decimal('balance', 10, 2)->default(0.00); // Outstanding balance

            $table->timestamps();
            $table->softDeletes(); // Optional
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folios');
    }
};
