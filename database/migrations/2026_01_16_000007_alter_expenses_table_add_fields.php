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
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('category')->nullable()->after('title');
            $table->foreignId('supplier_id')->nullable()->after('category')->constrained('suppliers')->onDelete('set null');
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'cheque'])->nullable()->after('supplier_id');
            $table->enum('status', ['pending', 'paid', 'approved'])->default('pending')->after('payment_method');
            $table->text('notes')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['category', 'supplier_id', 'payment_method', 'status', 'notes']);
        });
    }
};
