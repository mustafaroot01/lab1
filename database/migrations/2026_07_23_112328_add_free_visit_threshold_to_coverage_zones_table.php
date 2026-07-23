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
            $table->decimal('free_visit_threshold', 10, 2)->nullable()->after('service_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coverage_zones', function (Blueprint $table) {
            $table->dropColumn('free_visit_threshold');
        });
    }
};
