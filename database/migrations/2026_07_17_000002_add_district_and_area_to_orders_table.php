<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'district_id')) {
                $table->foreignId('district_id')->nullable()->after('branch_id')->constrained('districts')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'area_id')) {
                $table->foreignId('area_id')->nullable()->after('district_id')->constrained('areas')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('area_id');
            $table->dropConstrainedForeignId('district_id');
        });
    }
};
