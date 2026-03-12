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
        Schema::create('split_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folio_id')->constrained('folios')->onDelete('cascade');
            $table->string('split_type')->default('equal'); // equal, percentage, amount
            $table->integer('number_of_splits')->default(2);
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('split_billing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('split_billing_id')->constrained('split_billings')->onDelete('cascade');
            $table->foreignId('guest_id')->nullable()->constrained('guests')->onDelete('set null');
            $table->string('guest_name');
            $table->decimal('amount', 10, 2);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'partial'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('split_billing_items');
        Schema::dropIfExists('split_billings');
    }
};
