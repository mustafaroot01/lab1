<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('districts', function (Blueprint $table) {
            if (!Schema::hasColumn('districts', 'service_fee')) {
                $table->decimal('service_fee', 10, 2)->nullable()->after('branch_id')->comment('أجور الزيارة المنزلية الخاصة بالقضاء');
            }
            if (!Schema::hasColumn('districts', 'free_threshold')) {
                $table->decimal('free_threshold', 10, 2)->nullable()->after('service_fee')->comment('الحد الأدنى للزيارة المجانية في هذا القضاء');
            }
        });
    }

    public function down(): void
    {
        Schema::table('districts', function (Blueprint $table) {
            $table->dropColumn(['service_fee', 'free_threshold']);
        });
    }
};
