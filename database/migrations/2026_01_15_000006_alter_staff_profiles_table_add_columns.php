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
        Schema::table('staff_profiles', function (Blueprint $table) {
            // Limit employee_id so unique index fits within MySQL's 1000-byte key length on utf8mb4
            $table->string('employee_id', 191)->unique()->nullable()->after('user_id');
            $table->date('hire_date')->nullable()->after('role_id');
            $table->date('birth_date')->nullable()->after('hire_date');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('birth_date');
            $table->string('emergency_contact_name')->nullable()->after('address');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->text('notes')->nullable()->after('emergency_contact_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'employee_id',
                'hire_date',
                'birth_date',
                'gender',
                'emergency_contact_name',
                'emergency_contact_phone',
                'notes'
            ]);
        });
    }
};
