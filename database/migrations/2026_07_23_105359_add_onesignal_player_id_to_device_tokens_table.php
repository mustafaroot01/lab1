<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * حذف عمود fcm_token القديم (Firebase) من جدول device_tokens
     * والإبقاء على onesignal_player_id فقط كنظام إشعارات موحّد
     */
    public function up(): void
    {
        Schema::table('device_tokens', function (Blueprint $table) {
            // حذف العمود القديم إن وُجد
            if (Schema::hasColumn('device_tokens', 'fcm_token')) {
                $table->dropColumn('fcm_token');
            }
            // إضافة OneSignal Player ID إن لم يكن موجوداً
            if (!Schema::hasColumn('device_tokens', 'onesignal_player_id')) {
                $table->string('onesignal_player_id')->nullable()->unique()->after('tokenable_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('device_tokens', function (Blueprint $table) {
            if (!Schema::hasColumn('device_tokens', 'fcm_token')) {
                $table->string('fcm_token')->nullable()->unique();
            }
            if (Schema::hasColumn('device_tokens', 'onesignal_player_id')) {
                $table->dropColumn('onesignal_player_id');
            }
        });
    }
};
