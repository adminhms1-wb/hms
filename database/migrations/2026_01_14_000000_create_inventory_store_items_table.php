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
        Schema::create('inventory_store_items', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Limit code so unique index fits within MySQL's 1000-byte key length on utf8mb4
            $table->string('code', 191)->unique(); // e.g. INV-001
            $table->string('name'); // Item name

            // Optional normalized category reference and plain text category
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('category')->nullable();

            $table->integer('stock')->default(0); // Current stock
            $table->integer('min_stock')->default(0); // Minimum stock threshold
            $table->string('unit')->default('pcs'); // Unit of measurement

            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_store_items');
    }
};

