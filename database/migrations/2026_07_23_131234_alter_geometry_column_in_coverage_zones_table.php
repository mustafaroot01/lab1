<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE coverage_zones DROP INDEX coverage_zones_geometry_spatial_index');
            } catch (\Exception $e) {
                // Index might not exist
            }
            DB::statement('ALTER TABLE coverage_zones MODIFY geometry LONGTEXT NULL');
        } else {
            Schema::table('coverage_zones', function (Blueprint $table) {
                $table->longText('geometry')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No safe downgrade for LONGTEXT back to GEOMETRY without data transformation
    }
};
