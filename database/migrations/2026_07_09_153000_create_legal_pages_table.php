<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // اسم الصفحة
            $table->string('slug')->unique(); // المعرف (مثلاً: terms, privacy)
            $table->longText('content')->nullable(); // المحتوى (يدعم Markdown)
            $table->boolean('is_active')->default(true); // نشط أو لا
            $table->timestamp('last_updated_at')->nullable(); // آخر تحديث
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_pages');
    }
};
