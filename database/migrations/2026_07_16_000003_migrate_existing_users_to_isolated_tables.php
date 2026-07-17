<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. نقل المشرفين والأدمنية من جدول users إلى جدول admins الجديد
        $admins = DB::table('users')->where('role', 'admin')->get();
        foreach ($admins as $admin) {
            DB::table('admins')->insertOrIgnore([
                'id'         => $admin->id,
                'name'       => $admin->name ?? 'مشرف',
                'email'      => !empty($admin->email) ? $admin->email : 'admin_' . $admin->id . '@lab.local',
                'phone'      => $admin->phone,
                'password'   => $admin->password ?? bcrypt('password'),
                'role'       => 'super_admin',
                'is_active'  => $admin->is_active ?? true,
                'created_at' => $admin->created_at ?? now(),
                'updated_at' => $admin->updated_at ?? now(),
            ]);
        }

        // 2. نقل المرضى من جدول users إلى جدول patients الجديد
        $patients = DB::table('users')->where('role', '!=', 'admin')->orWhereNull('role')->get();
        foreach ($patients as $patient) {
            DB::table('patients')->insertOrIgnore([
                'id'                   => $patient->id,
                'name'                 => $patient->name,
                'phone'                => $patient->phone ?? ('077999999' . $patient->id),
                'email'                => $patient->email,
                'birth_date'           => $patient->birth_date ?? null,
                'gender'               => $patient->gender ?? null,
                'district_id'          => $patient->district_id ?? null,
                'area_id'              => $patient->area_id ?? null,
                'is_profile_completed' => $patient->is_profile_completed ?? false,
                'agreed_to_terms'      => $patient->agreed_to_terms ?? false,
                'is_active'            => $patient->is_active ?? true,
                'otp_code'             => $patient->otp_code ?? null,
                'otp_expires_at'       => $patient->otp_expires_at ?? null,
                'created_at'           => $patient->created_at ?? now(),
                'updated_at'           => $patient->updated_at ?? now(),
            ]);
        }

        // 3. إضافة عمود patient_id في الجداول المرتبطة بالمريض وجعله المفتاح الأساسي للربط
        $tables = [
            'orders',
            'patient_chronic_diseases',
            'patient_medications',
            'patient_allergies',
            'conversations',
            'coupon_usages'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'patient_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->unsignedBigInteger('patient_id')->nullable()->after('id');
                });

                // نسخ القيم القديمة من user_id إلى patient_id
                if (Schema::hasColumn($tableName, 'user_id')) {
                    DB::statement("UPDATE {$tableName} SET patient_id = user_id WHERE user_id IS NOT NULL");
                    
                    // جعل user_id قابل للـ null في حال لم يكن كذلك لتجنب أي تعارض
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->unsignedBigInteger('user_id')->nullable()->change();
                    });
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'orders',
            'patient_chronic_diseases',
            'patient_medications',
            'patient_allergies',
            'conversations',
            'coupon_usages'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'patient_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('patient_id');
                });
            }
        }
    }
};
