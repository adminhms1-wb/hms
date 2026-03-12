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
        if (!Schema::hasTable('refunds')) {
            Schema::create('refunds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');
                $table->foreignId('reservation_id')->nullable()->constrained('reservations')->onDelete('set null');
                $table->foreignId('folio_id')->nullable()->constrained('folios')->onDelete('set null');
                // Limit reference_number so unique index fits within MySQL's 1000-byte key length on utf8mb4
                $table->string('reference_number', 191)->unique();
                $table->decimal('refund_amount', 10, 2);
                $table->enum('refund_method', ['cash', 'card', 'bank_transfer', 'original_method'])->default('original_method');
                $table->enum('status', ['pending', 'approved', 'processed', 'completed', 'cancelled'])->default('pending');
                $table->text('reason');
                $table->date('refund_date');
                $table->date('processed_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            // Existing table: ensure reference_number length is safe and unique
            Schema::table('refunds', function (Blueprint $table) {
                if (Schema::hasColumn('refunds', 'reference_number')) {
                    $table->string('reference_number', 191)->change();
                    $table->unique('reference_number', 'refunds_reference_number_unique');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
