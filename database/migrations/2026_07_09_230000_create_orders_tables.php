<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // جدول الطلبات الرئيسي
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('technicians')->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();

            // الحالة
            $table->enum('status', [
                'pending',          // قيد الانتظار
                'confirmed',        // مؤكد
                'on_the_way',       // الفني في الطريق
                'sample_collected', // تم سحب العينة
                'results_ready',    // النتيجة جاهزة
                'delivered',        // تم التسليم
                'cancelled',        // ملغي
            ])->default('pending');

            // الأسعار
            $table->decimal('subtotal', 10, 2)->default(0);       // مجموع التحاليل/الباقات
            $table->decimal('service_fee', 10, 2)->default(0);    // أجور الخدمة المنزلية
            $table->decimal('discount_amount', 10, 2)->default(0);// قيمة الخصم
            $table->decimal('total', 10, 2)->default(0);          // الإجمالي النهائي

            // تفاصيل الزيارة
            $table->date('visit_date');                           // تاريخ الزيارة
            $table->string('visit_time', 10);                     // الوقت مثل: 08:00
            $table->enum('visit_period', ['morning', 'noon', 'evening']); // الفترة

            // الموقع
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 11, 7)->nullable();
            $table->text('address_text')->nullable();              // عنوان نصي

            // معلومات إضافية اختيارية
            $table->string('doctor_name')->nullable();             // اسم الطبيب المرسِل
            $table->string('referral_image')->nullable();          // صورة الراجعة الطبية
            $table->text('notes')->nullable();                     // ملاحظات للفني

            // ملاحظات الإلغاء
            $table->string('cancel_reason')->nullable();

            $table->timestamps();

            // الفهارس المركبة لتحسين السرعة الفائقة في شاشات المريض ولوحة الإدارة
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        // جدول بنود الطلب
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->enum('item_type', ['test', 'package']); // نوع العنصر: تحليل أو باقة
            $table->unsignedBigInteger('item_id');          // معرف التحليل أو الباقة
            $table->string('name_ar');                      // الاسم المخزون وقت الطلب
            $table->decimal('price', 10, 2)->default(0);   // السعر وقت الطلب
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
