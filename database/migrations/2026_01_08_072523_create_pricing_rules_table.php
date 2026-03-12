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
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->nullable()->constrained('room_types')->onDelete('cascade');
            $table->enum('rule_type', ['weekend', 'peak', 'holiday'])->default('weekend');
            $table->string('name')->nullable(); // e.g., "Weekend Pricing", "Peak Season"
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('price_multiplier', 5, 2)->default(1.00); // e.g., 1.20 = 20% increase
            $table->decimal('fixed_price', 10, 2)->nullable(); // Optional fixed price override
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
