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
        Schema::create('hotels', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('name');
            $table->string('logo')->nullable(); // logo file path, optional
            $table->text('address');
            $table->decimal('tax_percentage', 8, 2)->default(0.00);
            $table->decimal('service_charge', 8, 2)->default(0.00);
            $table->time('check_in_time')->default('14:00:00'); // default 2 PM
            $table->time('check_out_time')->default('12:00:00'); // default 12 PM
            $table->timestamps();
            $table->softDeletes(); // optional, for soft deletion
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
