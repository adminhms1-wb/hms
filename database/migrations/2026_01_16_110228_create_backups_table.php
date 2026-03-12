<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->enum('type', ['auto', 'manual', 'full', 'tables'])->default('manual');
            $table->bigInteger('size')->default(0);
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->string('file_path');
            $table->timestamp('backup_date')->useCurrent();
            $table->integer('retention_days')->nullable();
            $table->text('tables')->nullable(); // JSON array for selected tables
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
