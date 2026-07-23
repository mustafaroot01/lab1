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
        Schema::table('coverage_zones', function (Blueprint $table) {
            $table->decimal('min_lat', 10, 8)->nullable()->after('geometry');
            $table->decimal('max_lat', 10, 8)->nullable()->after('min_lat');
            $table->decimal('min_lng', 11, 8)->nullable()->after('max_lat');
            $table->decimal('max_lng', 11, 8)->nullable()->after('min_lng');
            $table->integer('grace_distance')->nullable()->after('priority')->comment('In meters. Overrides default if set.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coverage_zones', function (Blueprint $table) {
            $table->dropColumn(['min_lat', 'max_lat', 'min_lng', 'max_lng', 'grace_distance']);
        });
    }
};
