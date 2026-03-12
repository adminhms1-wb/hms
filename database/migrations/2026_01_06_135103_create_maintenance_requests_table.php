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
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign keys
            $table->foreignId('room_id')
                  ->constrained('rooms')
                  ->onDelete('cascade');

            // Issue description
            $table->text('issue');

            // Status of maintenance
            $table->enum('status', [
                'reported',
                'in_progress',
                'resolved',
                'cancelled'
            ])->default('reported');

            // Who reported the issue (staff or guest)
            $table->foreignId('reported_by')
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
        Schema::dropIfExists('maintenance_requests');
    }
};
