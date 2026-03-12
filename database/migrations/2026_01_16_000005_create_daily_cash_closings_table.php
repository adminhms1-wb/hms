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
        Schema::create('daily_cash_closings', function (Blueprint $table) {
            $table->id();
            $table->date('closing_date');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('opening_cash', 10, 2)->default(0.00);
            $table->decimal('cash_received', 10, 2)->default(0.00);
            $table->decimal('cash_paid', 10, 2)->default(0.00);
            $table->decimal('card_received', 10, 2)->default(0.00);
            $table->decimal('bank_transfer_received', 10, 2)->default(0.00);
            $table->decimal('online_received', 10, 2)->default(0.00);
            $table->decimal('expected_cash', 10, 2)->default(0.00);
            $table->decimal('actual_cash', 10, 2)->default(0.00);
            $table->decimal('difference', 10, 2)->default(0.00);
            $table->enum('status', ['open', 'closed', 'verified'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['closing_date', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_cash_closings');
    }
};
