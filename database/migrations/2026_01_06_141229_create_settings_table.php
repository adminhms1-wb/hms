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
        Schema::create('settings', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Limit key so unique index fits within MySQL's 1000-byte key length on utf8mb4
            $table->string('key', 191)->unique(); // Setting key (e.g., hotel_name, tax_rate)
            $table->text('value')->nullable(); // Setting value (string, JSON, etc.)

            $table->timestamps();
            $table->softDeletes(); // Optional
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
