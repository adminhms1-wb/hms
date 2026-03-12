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
        Schema::create('room_service_orders', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign keys
            $table->foreignId('room_id')
                  ->nullable()
                  ->constrained('rooms')
                  ->onDelete('cascade');

            $table->foreignId('guest_id')
                  ->nullable()
                  ->constrained('guests')
                  ->onDelete('cascade');

            // Use a simple nullable foreign key column here without an immediate
            // foreign key constraint so this migration does not depend on the
            // exact creation order of the `reservations` table on different systems.
            $table->foreignId('reservation_id')
                  ->nullable();

            $table->foreignId('assigned_staff_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            // Order details
            $table->string('room_number')->nullable();
            $table->string('guest_name')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('tax', 10, 2)->default(0.00);

            // Order status
            $table->enum('status', [
                'NEW',
                'KOT',
                'PREPARING',
                'OUT_FOR_DELIVERY',
                'DELIVERED',
                'CANCELLED'
            ])->default('NEW');

            // Time tracking
            $table->timestamp('order_time')->useCurrent();
            $table->timestamp('kitchen_time')->nullable();
            $table->timestamp('delivery_time')->nullable();
            $table->timestamp('delivered_time')->nullable();

            // Charges posting
            $table->boolean('charges_posted')->default(false);
            $table->timestamp('charges_posted_at')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Optional
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_service_orders');
    }
};
