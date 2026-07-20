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
        // 1. Drop foreign keys and columns from 'orders'
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'area_id')) {
                    $table->dropForeign(['area_id']);
                    $table->dropColumn('area_id');
                }
                if (Schema::hasColumn('orders', 'lat')) {
                    $table->dropColumn('lat');
                }
                if (Schema::hasColumn('orders', 'lng')) {
                    $table->dropColumn('lng');
                }
            });
        }

        // 2. Drop foreign keys and columns from 'patients'
        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                if (Schema::hasColumn('patients', 'area_id')) {
                    $table->dropForeign(['area_id']);
                    $table->dropColumn('area_id');
                }
                if (Schema::hasColumn('patients', 'latitude')) {
                    $table->dropColumn('latitude');
                }
                if (Schema::hasColumn('patients', 'longitude')) {
                    $table->dropColumn('longitude');
                }
            });
        }

        // 3. Drop foreign keys and columns from 'users'
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'area_id')) {
                    $table->dropForeign(['area_id']);
                    $table->dropColumn('area_id');
                }
                if (Schema::hasColumn('users', 'latitude')) {
                    $table->dropColumn('latitude');
                }
                if (Schema::hasColumn('users', 'longitude')) {
                    $table->dropColumn('longitude');
                }
            });
        }

        // 4. Drop 'areas' table
        Schema::dropIfExists('areas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not supporting down migration since data loss is intentional
    }
};
