<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('assigned_to_user_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable()->after('assigned_to_user_id');
            $table->index('assigned_to_user_id', 'conv_assigned_user_idx');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('sender_id');
            $table->index('is_system', 'msg_is_system_idx');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['assigned_to_user_id']);
            $table->dropIndex('conv_assigned_user_idx');
            $table->dropColumn(['assigned_to_user_id', 'assigned_at']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('msg_is_system_idx');
            $table->dropColumn('is_system');
        });
    }
};
