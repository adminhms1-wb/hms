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
         Schema::create('suppliers', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('name'); // Supplier name
            $table->string('contact')->nullable(); // Phone/email contact
            $table->timestamps();
            $table->softDeletes(); // Optional soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
