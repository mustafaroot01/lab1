<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. جدول الأمراض المزمنة للمريض
        Schema::create('patient_chronic_diseases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('disease_name');
            $table->string('severity', 20)->default('medium'); // low, medium, high
            $table->date('diagnosis_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. جدول الأدوية الحالية والسابقة للمريض
        Schema::create('patient_medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('medication_name');
            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable();
            $table->date('start_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. جدول الحساسية للمريض
        Schema::create('patient_allergies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('allergen');
            $table->string('severity', 20)->default('medium'); // low, medium, high
            $table->string('reaction')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_allergies');
        Schema::dropIfExists('patient_medications');
        Schema::dropIfExists('patient_chronic_diseases');
    }
};
