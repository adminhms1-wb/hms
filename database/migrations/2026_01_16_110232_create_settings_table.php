<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                // Limit key so unique index fits within MySQL's 1000-byte key length on utf8mb4
                $table->string('key', 191)->unique();
                $table->text('value')->nullable();
                $table->string('type')->default('string'); // string, json, boolean, integer
                $table->string('group')->default('general'); // auto_backup, authentication, etc.
                $table->timestamps();
            });
        } else {
            // Table already exists: best-effort to ensure key column is length 191
            try {
                // Shrink key column if it was previously created as varchar(255)
                DB::statement("ALTER TABLE settings MODIFY `key` VARCHAR(191) NOT NULL");
            } catch (\Throwable $e) {
                // Ignore if the column is already 191 or the alter fails harmlessly
            }

            // Add missing columns if table exists
            Schema::table('settings', function (Blueprint $table) {
                if (!Schema::hasColumn('settings', 'type')) {
                    $table->string('type')->default('string')->after('value');
                }
                if (!Schema::hasColumn('settings', 'group')) {
                    $table->string('group')->default('general')->after('type');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
