<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_infos', function (Blueprint $table) {
            $table->id();
            $table->string('channel_type'); // phone, whatsapp, facebook, telegram, instagram, working_hours, address, email
            $table->string('title'); // عنوان وسيلة التواصل (مثال: اتصل بنا، خدمة العملاء عبر واتساب)
            $table->text('value'); // القيمة (رقم الهاتف، الرابط، نص العنوان، إلخ)
            $table->integer('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_infos');
    }
};
