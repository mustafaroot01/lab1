<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // جدول الأقضية
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم القضاء
            $table->string('governorate')->default('ديالى'); // المحافظة
            $table->integer('sort_order')->default(1);
            $table->boolean('is_active')->default(true); // تظهر في التطبيق أم لا
            $table->timestamps();
        });

        // جدول المناطق — كل منطقة تتبع قضاء
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            $table->string('name'); // اسم المنطقة
            $table->integer('sort_order')->default(1);
            $table->boolean('is_active')->default(true); // تظهر في التطبيق أم لا
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
        Schema::dropIfExists('districts');
    }
};
