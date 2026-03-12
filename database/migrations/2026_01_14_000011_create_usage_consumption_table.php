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
        if (!Schema::hasTable('usage_consumption')) {
            Schema::create('usage_consumption', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('item_id');
                $table->enum('source', ['restaurant', 'room_service']);
                $table->integer('quantity')->default(0);
                $table->date('date');
                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_consumption');
    }
};

