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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('folio_id')->nullable()->after('reference_id')->constrained('folios')->onDelete('set null');
            $table->boolean('is_partial')->default(false)->after('status');
            $table->boolean('is_advance')->default(false)->after('is_partial');
            $table->decimal('remaining_balance', 10, 2)->nullable()->after('is_advance');
            $table->text('notes')->nullable()->after('remaining_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['folio_id']);
            $table->dropColumn(['folio_id', 'is_partial', 'is_advance', 'remaining_balance', 'notes']);
        });
    }
};
