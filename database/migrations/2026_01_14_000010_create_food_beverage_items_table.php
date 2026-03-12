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
        if (!Schema::hasTable('food_beverage_items')) {
            Schema::create('food_beverage_items', function (Blueprint $table) {
                $table->id();
                $table->string('code', 191)->unique();
                $table->string('name');
                $table->string('category');
                $table->string('unit')->default('piece');
                $table->integer('stock_level')->default(0);
                $table->integer('min_stock')->default(0);
                $table->decimal('unit_price', 10, 2)->default(0.00);
                $table->date('expiry_date')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_beverage_items');
    }
};

