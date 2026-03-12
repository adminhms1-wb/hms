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
        Schema::create('folio_items', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign key to folio
            $table->foreignId('folio_id')
                  ->constrained('folios')
                  ->onDelete('cascade');

            $table->string('description'); // Item description (e.g., Room charges, Dinner, Spa)
            $table->decimal('amount', 10, 2)->default(0.00); // Amount for this item
            $table->string('module')->nullable(); // Module source (room, restaurant, service, etc.)

            $table->timestamps();
            $table->softDeletes(); // Optional
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folio_items');
    }
};
