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
        Schema::create('room_type_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('room_types')->onDelete('cascade');
            $table->time('checkin_time');
            $table->time('checkout_time');
            $table->boolean('early_checkin_allowed')->default(false);
            $table->time('early_checkin_time')->nullable();
            $table->boolean('late_checkout_allowed')->default(false);
            $table->time('late_checkout_time')->nullable();
            $table->decimal('late_checkout_fee', 10, 2)->nullable()->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            // Ensure one time configuration per room type
            $table->unique('room_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_type_times');
    }
};
