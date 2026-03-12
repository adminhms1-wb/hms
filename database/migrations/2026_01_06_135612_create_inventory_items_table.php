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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('name'); // Item name
            $table->string('category')->nullable(); // Category or type
            $table->integer('quantity')->default(0); // Current stock
            $table->date('expiry_date')->nullable(); // Optional expiry
            $table->integer('threshold')->default(0); // Minimum stock threshold
            $table->timestamps();
            $table->softDeletes(); // Optional
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
