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

        // حذف العمود القديم وإعادة تسمية الجديد
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });
    }

    public function down(): void
    {
        // لا حاجة لعكسها
    }
};
