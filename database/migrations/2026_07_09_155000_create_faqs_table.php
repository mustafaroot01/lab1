<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // general, orders, payments, results, technician
            $table->text('question'); // السؤال
            $table->text('answer'); // الجواب
            $table->integer('sort_order')->default(1); // الترتيب
            $table->boolean('is_active')->default(true); // نشط أو لا
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
