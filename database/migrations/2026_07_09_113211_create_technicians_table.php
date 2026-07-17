<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technicians', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('password');
            $table->string('address')->nullable();
            $table->string('specialty')->nullable();        // التخصص
            $table->boolean('has_transport')->default(false);   // وسيلة نقل
            $table->boolean('has_equipment')->default(false);   // حقيبة ومعدات
            $table->string('id_front_image')->nullable();   // صورة الهوية وجه
            $table->string('id_back_image')->nullable();    // صورة الهوية ظهر
            $table->string('district_id_image')->nullable(); // هوية الدائرة
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'suspended', 'on_leave'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technicians');
    }
};
