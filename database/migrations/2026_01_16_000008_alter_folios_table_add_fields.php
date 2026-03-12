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
        Schema::table('folios', function (Blueprint $table) {
            $table->foreignId('guest_id')->nullable()->after('reservation_id')->constrained('guests')->onDelete('set null');
            $table->decimal('subtotal', 10, 2)->default(0.00)->after('total');
            $table->decimal('tax_amount', 10, 2)->default(0.00)->after('subtotal');
            $table->decimal('service_charge', 10, 2)->default(0.00)->after('tax_amount');
            $table->decimal('discount', 10, 2)->default(0.00)->after('service_charge');
            $table->enum('status', ['open', 'closed', 'cancelled'])->default('open')->after('balance');
            $table->date('folio_date')->nullable()->after('status');
            $table->text('notes')->nullable()->after('folio_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('folios', function (Blueprint $table) {
            $table->dropForeign(['guest_id']);
            $table->dropColumn(['guest_id', 'subtotal', 'tax_amount', 'service_charge', 'discount', 'status', 'folio_date', 'notes']);
        });
    }
};
