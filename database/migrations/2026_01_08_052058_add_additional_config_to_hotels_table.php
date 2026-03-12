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
        Schema::table('hotels', function (Blueprint $table) {
            // Place new flags after existing columns without relying on a timezone column
            $table->boolean('online_booking')->default(false);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('auto_checkin')->default(false);
            $table->boolean('maintenance_mode')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['online_booking', 'email_notifications', 'auto_checkin', 'maintenance_mode']);
        });
    }
};
