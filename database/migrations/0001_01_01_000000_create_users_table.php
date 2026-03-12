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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // id as primary key
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('name');
            // Limit email length so unique index fits within MySQL's 1000-byte key length on utf8mb4
            $table->string('email', 191)->unique();
            $table->string('password');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps(); // created_at and updated_at
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            // Limit email length so primary key index fits within MySQL's 1000-byte key length on utf8mb4
            $table->string('email', 191)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
