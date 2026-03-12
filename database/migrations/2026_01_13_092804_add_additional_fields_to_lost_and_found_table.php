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
        Schema::table('lost_and_found', function (Blueprint $table) {
            $table->text('description')->nullable()->after('item');
            $table->foreignId('found_by')->nullable()->after('description')->constrained('users')->nullOnDelete();
            $table->foreignId('claimed_by')->nullable()->after('found_by')->constrained('users')->nullOnDelete();
            $table->date('claimed_date')->nullable()->after('claimed_by');
            $table->string('location')->nullable()->after('claimed_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lost_and_found', function (Blueprint $table) {
            $table->dropForeign(['found_by']);
            $table->dropForeign(['claimed_by']);
            $table->dropColumn(['description', 'found_by', 'claimed_by', 'claimed_date', 'location']);
        });
    }
};
