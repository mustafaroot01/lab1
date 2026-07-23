<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('coverage_zone_id')->nullable()->after('coupon_id')->constrained('coverage_zones')->nullOnDelete();
            $table->json('coverage_zone_snapshot')->nullable()->after('coverage_zone_id');
            // service_fee exists, lat and lng exist.
            $table->string('building')->nullable()->after('address_text');
            $table->string('floor')->nullable()->after('building');
            $table->string('apartment')->nullable()->after('floor');
            $table->string('landmark')->nullable()->after('apartment');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['coverage_zone_id']);
            $table->dropColumn([
                'coverage_zone_id', 
                'coverage_zone_snapshot', 
                'building', 
                'floor', 
                'apartment', 
                'landmark'
            ]);
        });
    }
};
