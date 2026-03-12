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
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign key to reservation
            $table->foreignId('reservation_id');

            // Checkout details
            $table->timestamp('checkout_time')->nullable(); // Checkout datetime
            $table->decimal('extra_charges', 10, 2)->default(0.00); // Any extra charges

            $table->timestamps();
            $table->softDeletes(); // Optional soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};
