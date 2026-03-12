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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id(); // primary key
            $table->string('name'); // permission name, e.g., 'Create User'
            // Limit slug so unique index fits within MySQL's 1000-byte key length on utf8mb4
            $table->string('slug', 191)->unique(); // permission slug, e.g., 'create_user'
            $table->string('module'); // module name, e.g., 'users', 'roles'
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes(); // optional, for soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
