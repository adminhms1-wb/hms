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
       Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign key to inventory item
            $table->foreignId('item_id')
                  ->constrained('inventory_items')
                  ->onDelete('cascade');

            // Transaction type: stock_in / stock_out
            $table->enum('type', ['stock_in', 'stock_out']);

            $table->integer('qty')->default(0); // Quantity moved
            $table->string('reference')->nullable(); // e.g., purchase_id, order_id, adjustment

            $table->timestamps();
            $table->softDeletes(); // Optional
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
