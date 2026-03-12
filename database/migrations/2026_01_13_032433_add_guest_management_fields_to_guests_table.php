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
        Schema::table('guests', function (Blueprint $table) {
            $table->enum('id_type', ['national_id', 'passport', 'driving_license', 'other'])->nullable()->after('id_number');
            $table->text('address')->nullable()->after('email');
            $table->date('date_of_birth')->nullable()->after('address');
            $table->string('nationality')->nullable()->after('date_of_birth');
            $table->boolean('is_vip')->default(false)->after('nationality');
            $table->text('allergies')->nullable()->after('is_vip');
            $table->json('preferences')->nullable()->after('allergies');
            $table->boolean('is_blacklisted')->default(false)->after('preferences');
            $table->text('blacklist_reason')->nullable()->after('is_blacklisted');
            $table->timestamp('flagged_at')->nullable()->after('blacklist_reason');
            $table->text('flagged_reason')->nullable()->after('flagged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn([
                'id_type',
                'address',
                'date_of_birth',
                'nationality',
                'is_vip',
                'allergies',
                'preferences',
                'is_blacklisted',
                'blacklist_reason',
                'flagged_at',
                'flagged_reason',
            ]);
        });
    }
};
