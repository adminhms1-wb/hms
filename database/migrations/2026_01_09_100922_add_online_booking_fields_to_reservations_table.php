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
            $table->string('external_booking_id')->nullable()->after('group_id');
            $table->string('webhook_url')->nullable()->after('external_booking_id');
            $table->string('payment_status')->nullable()->after('webhook_url');
            $table->string('payment_method')->nullable()->after('payment_status');
            $table->text('online_booking_metadata')->nullable()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['external_booking_id', 'webhook_url', 'payment_status', 'payment_method', 'online_booking_metadata']);
        });
    }
};
