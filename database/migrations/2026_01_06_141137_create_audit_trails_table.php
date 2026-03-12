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
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id(); // Primary Key

            $table->string('reference'); // Table or record reference (e.g., reservations:12)
            $table->text('old_value')->nullable(); // JSON/text of old data
            $table->text('new_value')->nullable(); // JSON/text of new data

            $table->foreignId('changed_by') // User who made the change
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes(); // Optional
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
