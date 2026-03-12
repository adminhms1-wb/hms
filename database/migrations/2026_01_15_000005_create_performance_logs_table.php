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
        Schema::create('performance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->date('review_date');
            $table->enum('category', ['attendance', 'task_completion', 'customer_service', 'teamwork', 'punctuality', 'other'])->default('other');
            $table->string('title');
            $table->text('description');
            $table->integer('rating')->default(0); // 1-5 scale
            $table->enum('type', ['positive', 'negative', 'neutral'])->default('neutral');
            $table->text('action_items')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_logs');
    }
};
