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
        Schema::create('restaurant_orders', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Order type: room_service or dine_in
            $table->enum('order_type', ['room_service', 'dine_in']);

            // Nullable: used only for room service orders
            $table->foreignId('room_id')
                  ->nullable()
                  ->constrained('rooms')
                  ->nullOnDelete();

            // Nullable: used only for dine-in orders
            $table->string('table_no')->nullable();

            // Order status
            $table->enum('status', [
                'pending',
                'preparing',
                'served',
                'completed',
                'cancelled'
            ])->default('pending');

            // Order total
            $table->decimal('total', 10, 2)->default(0.00);

            $table->timestamps();
            $table->softDeletes(); // Optional
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_orders');
    }
};
