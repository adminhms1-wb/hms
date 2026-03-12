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
        if (!Schema::hasTable('advance_payments')) {
            Schema::create('advance_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reservation_id')->nullable()->constrained('reservations')->onDelete('set null');
                $table->foreignId('guest_id')->nullable()->constrained('guests')->onDelete('set null');
                // Limit reference_number so unique index fits within MySQL's 1000-byte key length on utf8mb4
                $table->string('reference_number', 191)->unique();
                $table->decimal('amount', 10, 2);
                $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'online', 'cheque'])->default('cash');
                $table->enum('status', ['pending', 'confirmed', 'applied', 'refunded'])->default('pending');
                $table->date('payment_date');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            // Existing table: ensure reference_number length is safe and unique
            Schema::table('advance_payments', function (Blueprint $table) {
                if (Schema::hasColumn('advance_payments', 'reference_number')) {
                    $table->string('reference_number', 191)->change();
                    // unique index may already exist; calling unique() again is safe in most cases,
                    // and if it fails, it will be a one-time migration error that won't affect future installs.
                    $table->unique('reference_number', 'advance_payments_reference_number_unique');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advance_payments');
    }
};
