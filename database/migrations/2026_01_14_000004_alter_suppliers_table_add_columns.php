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
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'company_name')) {
                $table->string('company_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('suppliers', 'email')) {
                $table->string('email')->nullable()->after('contact');
            }
            if (!Schema::hasColumn('suppliers', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('suppliers', 'mobile')) {
                $table->string('mobile')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('suppliers', 'address')) {
                $table->text('address')->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('suppliers', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (!Schema::hasColumn('suppliers', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('suppliers', 'country')) {
                $table->string('country')->nullable()->after('state');
            }
            if (!Schema::hasColumn('suppliers', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('country');
            }
            if (!Schema::hasColumn('suppliers', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('postal_code');
            }
            if (!Schema::hasColumn('suppliers', 'tax_id')) {
                $table->string('tax_id')->nullable()->after('contact_person');
            }
            if (!Schema::hasColumn('suppliers', 'notes')) {
                $table->text('notes')->nullable()->after('tax_id');
            }
            if (!Schema::hasColumn('suppliers', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $columns = [
                'company_name', 'email', 'phone', 'mobile', 'address',
                'city', 'state', 'country', 'postal_code', 'contact_person',
                'tax_id', 'notes', 'is_active'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('suppliers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
