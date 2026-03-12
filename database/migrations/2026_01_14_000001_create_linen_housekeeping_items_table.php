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
        Schema::create('linen_housekeeping_items', function (Blueprint $table) {
            $table->id();

            // Limit code so unique index fits within MySQL's 1000-byte key length on utf8mb4
            $table->string('code', 191)->unique(); // e.g. LIN-001, HK-001
            $table->string('name');

            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('category')->nullable();

            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0);
            $table->string('unit')->default('pcs');
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
        Schema::dropIfExists('linen_housekeeping_items');
    }
};

