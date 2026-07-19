<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite: تغيير الـ status من enum إلى string عادي يدعم كل الحالات
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status_new')->default('pending')->after('status');
        });

        // نسخ البيانات القديمة مع تعديل الأسماء
        DB::table('orders')->get()->each(function ($order) {
            $newStatus = match ($order->status) {
                'results_ready' => 'in_progress',
                'delivered'     => 'completed',
                default         => $order->status,
            };
            DB::table('orders')->where('id', $order->id)->update(['status_new' => $newStatus]);
        });

        // إسقاط الفهارس المرتبطة بعمود status قبل حذفه (إلزامي على SQLite)
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['status', 'created_at']);
        });

        // حذف العمود القديم وإعادة تسمية الجديد
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });

        // إعادة بناء الفهارس بعد إعادة تسمية العمود
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        // لا حاجة لعكسها
    }
};
