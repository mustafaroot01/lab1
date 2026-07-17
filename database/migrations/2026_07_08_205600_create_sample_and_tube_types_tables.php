<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sample_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar')->unique();
            $table->string('name_en')->nullable();
            $table->string('code')->nullable();
            $table->string('icon')->default('tabler-test-pipe');
            $table->string('color')->default('primary');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::create('tube_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar')->unique();
            $table->string('name_en')->nullable();
            $table->string('code')->nullable();
            $table->string('cap_color')->nullable();
            $table->string('color_hex')->default('#9c27b0');
            $table->string('additive')->nullable();
            $table->string('icon')->default('tabler-color-swatch');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::table('medical_tests', function (Blueprint $table) {
            $table->foreignId('sample_type_id')->nullable()->constrained('sample_types')->onDelete('set null');
            $table->foreignId('tube_type_id')->nullable()->constrained('tube_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_tests', function (Blueprint $table) {
            $table->dropForeign(['sample_type_id']);
            $table->dropForeign(['tube_type_id']);
            $table->dropColumn(['sample_type_id', 'tube_type_id']);
        });

        Schema::dropIfExists('tube_types');
        Schema::dropIfExists('sample_types');
    }
};
