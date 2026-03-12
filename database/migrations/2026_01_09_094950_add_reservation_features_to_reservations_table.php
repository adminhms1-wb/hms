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
        Schema::table('reservations', function (Blueprint $table) {
            $table->enum('booking_type', ['walk_in', 'advance', 'online'])->default('advance')->after('status');
            $table->string('group_id')->nullable()->after('booking_type');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('total_amount');
            $table->text('cancellation_reason')->nullable()->after('refund_amount');
            $table->dateTime('checked_in_at')->nullable()->after('check_out_date');
            $table->dateTime('checked_out_at')->nullable()->after('checked_in_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['booking_type', 'group_id', 'refund_amount', 'cancellation_reason', 'checked_in_at', 'checked_out_at']);
        });
    }
};
