<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * ملاحظة: هذا تمهيد جزئي لموديول الدردشة فقط (لتمييز مُرسِل الرسالة أدمن/مريض)
     * ولا يُعتبر بديلاً عن نظام مصادقة الأدمن الكامل (مرحلة 0 المؤجلة).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('patient')->after('id');
        });

        // حساب أدمن مؤقت لتشغيل موديول الدردشة قبل اكتمال نظام مصادقة الأدمن الحقيقي
        DB::table('users')->insertOrIgnore([
            'name'                  => 'الدعم الفني',
            'phone'                 => '07700000000',
            'email'                 => 'support@lab.local',
            'password'              => bcrypt('changeme123'),
            'role'                  => 'admin',
            'is_profile_completed'  => true,
            'is_active'             => true,
            'agreed_to_terms'       => true,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
