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
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('priority')->default('normal')->after('status'); // low, normal, high, urgent
            $table->foreignId('assigned_to')->nullable()->after('priority')->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->after('assigned_to');
            $table->text('notes')->nullable()->after('resolved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn(['priority', 'assigned_to', 'resolved_at', 'notes']);
        });
    }
};
