<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->unique()->after('name');
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->date('birth_date')->nullable()->after('phone');
            $table->string('gender', 20)->nullable()->after('birth_date');
            $table->foreignId('district_id')->nullable()->after('gender')->constrained('districts')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->after('district_id')->constrained('areas')->nullOnDelete();
            $table->boolean('is_profile_completed')->default(false)->after('area_id');
            $table->boolean('agreed_to_terms')->default(false)->after('is_profile_completed');
            $table->string('otp_code', 10)->nullable()->after('agreed_to_terms');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['district_id']);
            $table->dropForeign(['area_id']);
            $table->dropColumn([
                'phone',
                'birth_date',
                'gender',
                'district_id',
                'area_id',
                'is_profile_completed',
                'agreed_to_terms',
                'otp_code',
                'otp_expires_at',
            ]);
        });
    }
};
