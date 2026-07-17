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
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'idx_orders_user_status');
            $table->index(['status', 'created_at'], 'idx_orders_status_created');
        });

        Schema::table('test_groups', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order'], 'idx_groups_active_sort');
        });

        Schema::table('medical_tests', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order'], 'idx_tests_active_sort');
            $table->index(['test_group_id', 'is_active', 'sort_order'], 'idx_tests_group_active_sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_user_status');
            $table->dropIndex('idx_orders_status_created');
        });

        Schema::table('test_groups', function (Blueprint $table) {
            $table->dropIndex('idx_groups_active_sort');
        });

        Schema::table('medical_tests', function (Blueprint $table) {
            $table->dropIndex('idx_tests_active_sort');
            $table->dropIndex('idx_tests_group_active_sort');
        });
    }
};
