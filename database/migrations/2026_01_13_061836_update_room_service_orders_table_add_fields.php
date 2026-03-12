<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('room_service_orders', function (Blueprint $table) {
            // Make existing foreign keys nullable (only if columns already exist)
            if (Schema::hasColumn('room_service_orders', 'room_id')) {
                $table->foreignId('room_id')->nullable()->change();
            }
            if (Schema::hasColumn('room_service_orders', 'guest_id')) {
                $table->foreignId('guest_id')->nullable()->change();
            }
            
            // Add new fields only if they do not already exist
            if (!Schema::hasColumn('room_service_orders', 'reservation_id')) {
                $table->foreignId('reservation_id')->nullable()->after('guest_id')->constrained('reservations')->onDelete('set null');
            }
            if (!Schema::hasColumn('room_service_orders', 'assigned_staff_id')) {
                $table->foreignId('assigned_staff_id')->nullable()->after('reservation_id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('room_service_orders', 'room_number')) {
                $table->string('room_number')->nullable()->after('assigned_staff_id');
            }
            if (!Schema::hasColumn('room_service_orders', 'guest_name')) {
                $table->string('guest_name')->nullable()->after('room_number');
            }
            if (!Schema::hasColumn('room_service_orders', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->default(0.00)->after('guest_name');
            }
            if (!Schema::hasColumn('room_service_orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0.00)->after('total_amount');
            }
            if (!Schema::hasColumn('room_service_orders', 'tax')) {
                $table->decimal('tax', 10, 2)->default(0.00)->after('subtotal');
            }
            
            // Update status enum
            $table->enum('status', ['NEW', 'KOT', 'PREPARING', 'OUT_FOR_DELIVERY', 'DELIVERED', 'CANCELLED'])->default('NEW')->change();
            
            // Add other time fields (ordered_at already exists, we'll keep it as order_time)
            if (!Schema::hasColumn('room_service_orders', 'kitchen_time')) {
                $table->timestamp('kitchen_time')->nullable()->after('ordered_at');
            }
            if (!Schema::hasColumn('room_service_orders', 'delivery_time')) {
                $table->timestamp('delivery_time')->nullable()->after('kitchen_time');
            }
            if (!Schema::hasColumn('room_service_orders', 'delivered_time')) {
                $table->timestamp('delivered_time')->nullable()->after('delivery_time');
            }
            
            // Charges posting
            if (!Schema::hasColumn('room_service_orders', 'charges_posted')) {
                $table->boolean('charges_posted')->default(false)->after('delivered_time');
            }
            if (!Schema::hasColumn('room_service_orders', 'charges_posted_at')) {
                $table->timestamp('charges_posted_at')->nullable()->after('charges_posted');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_service_orders', function (Blueprint $table) {
            $table->dropColumn([
                'reservation_id',
                'assigned_staff_id',
                'room_number',
                'guest_name',
                'total_amount',
                'subtotal',
                'tax',
                'kitchen_time',
                'delivery_time',
                'delivered_time',
                'charges_posted',
                'charges_posted_at',
            ]);
            $table->enum('status', ['pending', 'preparing', 'delivered', 'cancelled'])->default('pending')->change();
        });
    }
};
