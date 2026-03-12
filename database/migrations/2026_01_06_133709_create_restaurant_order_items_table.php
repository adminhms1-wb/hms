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
        Schema::create('restaurant_order_items', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign keys
            $table->foreignId('order_id')
                  ->constrained('restaurant_orders')
                  ->onDelete('cascade');

            $table->foreignId('menu_item_id')
                  ->constrained('menu_items')
                  ->onDelete('restrict');

            // Item details
            $table->integer('qty')->default(1);
            $table->decimal('price', 10, 2); // price at time of order

            $table->timestamps();

            // Prevent duplicate items in same order
            $table->unique(['order_id', 'menu_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_order_items');
    }
};
