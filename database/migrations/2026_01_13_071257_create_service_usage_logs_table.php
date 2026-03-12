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
        Schema::create('service_usage_logs', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('service_id')
                  ->constrained('services')
                  ->onDelete('cascade');
            
            $table->foreignId('service_booking_id')
                  ->nullable()
                  ->constrained('service_bookings')
                  ->nullOnDelete();
            
            $table->foreignId('guest_id')
                  ->nullable()
                  ->constrained('guests')
                  ->nullOnDelete();
            
            $table->foreignId('room_id')
                  ->nullable()
                  ->constrained('rooms')
                  ->nullOnDelete();
            
            $table->foreignId('reservation_id')
                  ->nullable()
                  ->constrained('reservations')
                  ->nullOnDelete();
            
            // Usage details
            $table->date('usage_date');
            $table->time('usage_time')->nullable();
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->enum('charge_type', ['room', 'direct'])->default('direct');
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            
            // Staff who processed
            $table->foreignId('processed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_usage_logs');
    }
};
