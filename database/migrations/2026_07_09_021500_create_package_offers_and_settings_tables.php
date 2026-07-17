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
        // System settings table for global toggles (e.g., packages global status)
        if (!Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        // Package Offers table
        if (!Schema::hasTable('package_offers')) {
            Schema::create('package_offers', function (Blueprint $table) {
                $table->id();
                $table->string('name_ar');
                $table->string('name_en')->nullable();
                $table->text('description_ar')->nullable();
                $table->text('description_en')->nullable();
                $table->decimal('original_price', 10, 2)->default(0);
                $table->decimal('discount_price', 10, 2)->nullable();
                $table->string('image')->nullable();
                $table->integer('sort_order')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Pivot table connecting package offers to medical tests
        if (!Schema::hasTable('package_offer_tests')) {
            Schema::create('package_offer_tests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_offer_id')->constrained('package_offers')->onDelete('cascade');
                $table->foreignId('medical_test_id')->constrained('medical_tests')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_offer_tests');
        Schema::dropIfExists('package_offers');
        Schema::dropIfExists('system_settings');
    }
};
