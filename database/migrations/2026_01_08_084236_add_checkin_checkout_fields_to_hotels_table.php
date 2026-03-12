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
            $table->boolean('early_checkin_allowed')->default(false)->after('check_out_time');
            $table->time('early_checkin_time')->nullable()->after('early_checkin_allowed');
            $table->boolean('late_checkout_allowed')->default(false)->after('early_checkin_time');
            $table->time('late_checkout_time')->nullable()->after('late_checkout_allowed');
            $table->decimal('late_checkout_fee', 10, 2)->nullable()->default(0)->after('late_checkout_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn([
                'early_checkin_allowed',
                'early_checkin_time',
                'late_checkout_allowed',
                'late_checkout_time',
                'late_checkout_fee'
            ]);
        });
    }
};
