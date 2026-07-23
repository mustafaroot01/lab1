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
        // Drop foreign keys and columns from coverage_zones
        Schema::table('coverage_zones', function (Blueprint $table) {
            if (Schema::hasColumn('coverage_zones', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
        });

        // Drop foreign keys and columns from orders
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
            if (Schema::hasColumn('orders', 'district_id')) {
                $table->dropForeign(['district_id']);
                $table->dropColumn('district_id');
            }
            if (Schema::hasColumn('orders', 'area_id')) {
                $table->dropForeign(['area_id']);
                $table->dropColumn('area_id');
            }
        });

        // Drop foreign keys and columns from patients
        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'district_id')) {
                $table->dropForeign(['district_id']);
                $table->dropColumn('district_id');
            }
            if (Schema::hasColumn('patients', 'area_id')) {
                $table->dropForeign(['area_id']);
                $table->dropColumn('area_id');
            }
        });

        // Drop branch_id from branch_services and rename it to services
        Schema::table('branch_services', function (Blueprint $table) {
            if (Schema::hasColumn('branch_services', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
        });
        Schema::rename('branch_services', 'services');

        // Drop users branch_id and district_id if they exist
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
            if (Schema::hasColumn('users', 'district_id')) {
                $table->dropForeign(['district_id']);
                $table->dropColumn('district_id');
            }
        });

        // Finally drop the legacy tables
        Schema::dropIfExists('areas');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('branches');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a destructive one-way migration. Down is intentionally left blank.
    }
};
