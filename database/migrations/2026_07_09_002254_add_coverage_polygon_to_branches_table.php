<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('coverage_type', 20)->default('polygon')->after('radius_km');
            $table->json('coverage_polygon')->nullable()->after('coverage_type');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['coverage_type', 'coverage_polygon']);
        });
    }
};
