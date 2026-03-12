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
        Schema::table('service_bookings', function (Blueprint $table) {
            // Check if columns exist before adding
            if (!Schema::hasColumn('service_bookings', 'time_slot_id')) {
                // Time slot - only add if service_time_slots table exists
                if (Schema::hasTable('service_time_slots')) {
                    $table->foreignId('time_slot_id')
                          ->nullable()
                          ->constrained('service_time_slots')
                          ->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('time_slot_id')->nullable();
                }
            }
            
            if (!Schema::hasColumn('service_bookings', 'assigned_staff_id')) {
                // Staff assignment
                $table->foreignId('assigned_staff_id')
                      ->nullable()
                      ->constrained('users')
                      ->nullOnDelete();
            }
            
            if (!Schema::hasColumn('service_bookings', 'charge_type')) {
                // Charge posting
                $table->enum('charge_type', ['room', 'direct'])->default('direct');
            }
            
            if (!Schema::hasColumn('service_bookings', 'reservation_id')) {
                $table->foreignId('reservation_id')
                      ->nullable()
                      ->constrained('reservations')
                      ->nullOnDelete();
            }
            
            if (!Schema::hasColumn('service_bookings', 'payment_status')) {
                // Payment details
                $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            }
            
            if (!Schema::hasColumn('service_bookings', 'payment_method')) {
                $table->string('payment_method')->nullable();
            }
            
            if (!Schema::hasColumn('service_bookings', 'amount')) {
                $table->decimal('amount', 10, 2)->default(0.00);
            }
            
            if (!Schema::hasColumn('service_bookings', 'paid_at')) {
                $table->timestamp('paid_at')->nullable();
            }
            
            if (!Schema::hasColumn('service_bookings', 'start_time')) {
                // Booking time
                $table->time('start_time')->nullable();
            }
            
            if (!Schema::hasColumn('service_bookings', 'end_time')) {
                $table->time('end_time')->nullable();
            }
            
            if (!Schema::hasColumn('service_bookings', 'notes')) {
                // Notes
                $table->text('notes')->nullable();
            }
        });
        
        // Add foreign key constraint for time_slot_id if table exists and constraint doesn't
        if (Schema::hasTable('service_time_slots') && Schema::hasColumn('service_bookings', 'time_slot_id')) {
            try {
                Schema::table('service_bookings', function (Blueprint $table) {
                    $table->foreign('time_slot_id')
                          ->references('id')
                          ->on('service_time_slots')
                          ->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Constraint might already exist, ignore
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {
            $table->dropForeign(['time_slot_id']);
            $table->dropForeign(['assigned_staff_id']);
            $table->dropForeign(['reservation_id']);
            $table->dropColumn([
                'time_slot_id',
                'assigned_staff_id',
                'charge_type',
                'reservation_id',
                'payment_status',
                'payment_method',
                'amount',
                'paid_at',
                'start_time',
                'end_time',
                'notes',
            ]);
        });
    }
};
