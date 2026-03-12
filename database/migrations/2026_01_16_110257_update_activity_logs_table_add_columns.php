<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_logs', 'model')) {
                $table->string('model')->nullable()->after('action');
            }
            if (!Schema::hasColumn('activity_logs', 'model_id')) {
                $table->unsignedBigInteger('model_id')->nullable()->after('model');
            }
            if (!Schema::hasColumn('activity_logs', 'description')) {
                $table->text('description')->nullable()->after('model_id');
            }
            if (!Schema::hasColumn('activity_logs', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('description');
            }
            if (!Schema::hasColumn('activity_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('activity_logs', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['model', 'model_id', 'description', 'ip_address', 'user_agent', 'updated_at']);
        });
    }
};
