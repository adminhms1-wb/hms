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
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Polymorphic reference (reservation, restaurant_order, invoice, etc.)
            $table->string('reference_type'); // e.g. reservation, restaurant_order
            $table->unsignedBigInteger('reference_id');

            // Payment details
            $table->decimal('amount', 10, 2);
            $table->enum('method', [
                'cash',
                'card',
                'bank_transfer',
                'online'
            ]);

            $table->enum('status', [
                'pending',
                'paid',
                'failed',
                'refunded'
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
        Schema::dropIfExists('payments');
    }
};
