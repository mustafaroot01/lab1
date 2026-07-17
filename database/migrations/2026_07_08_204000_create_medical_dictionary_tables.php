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
        Schema::create('test_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('key')->unique();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('medical_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_group_id')->constrained('test_groups')->onDelete('cascade');
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('key')->nullable();
            $table->string('sample_type')->nullable();
            $table->string('tube_type')->nullable();
            $table->boolean('fasting_required')->default(false);
            $table->string('result_time')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('platform_price', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index(['test_group_id', 'is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_tests');
        Schema::dropIfExists('test_groups');
    }
};
