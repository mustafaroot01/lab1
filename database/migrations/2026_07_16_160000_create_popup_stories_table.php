<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('popup_stories', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // عنوان داخلي للإدارة
            $table->string('image_path'); // مسار الصورة الطولية (9:16) بنمط ستوري إنستغرام
            $table->unsignedInteger('duration_seconds')->default(6); // مدة شريط التقدم بالثواني (مثلاً 6 ثوانٍ)
            $table->enum('display_frequency', ['always', 'once_per_day', 'once_per_session'])->default('once_per_day'); // تكرار الظهور
            $table->string('button_text')->nullable(); // نص زر الإجراء (CTA)
            $table->string('button_link_type')->default('none'); // package, test, coupon, external, none
            $table->string('button_link_target')->nullable(); // معرف الباقة أو الفحص أو الرابط الخارجي
            $table->timestamp('start_at')->nullable(); // تاريخ بدء العرض
            $table->timestamp('end_at')->nullable(); // تاريخ انتهاء العرض
            $table->integer('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('views_count')->default(0); // إحصائيات المشاهدات
            $table->unsignedBigInteger('clicks_count')->default(0); // إحصائيات النقرات
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_stories');
    }
};
