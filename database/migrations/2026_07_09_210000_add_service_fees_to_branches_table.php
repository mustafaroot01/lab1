<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->decimal('service_fee', 10, 2)->default(5000)->after('radius_km');
            $table->decimal('urgent_fee', 10, 2)->default(10000)->after('service_fee');
            $table->decimal('free_threshold', 10, 2)->nullable()->after('urgent_fee');
            $table->string('fee_notes')->nullable()->after('free_threshold');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['service_fee', 'urgent_fee', 'free_threshold', 'fee_notes']);
        });
    }
};
