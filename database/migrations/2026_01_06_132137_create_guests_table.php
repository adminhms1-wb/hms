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
         Schema::create('guests', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('name');
            $table->string('phone')->nullable(); // optional phone
            $table->string('email')->nullable(); // optional email
            $table->string('id_number')->nullable(); // national ID, passport, etc.
            $table->text('notes')->nullable(); // additional notes about guest
            $table->timestamps();
            $table->softDeletes(); // optional, allows soft deletion
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
