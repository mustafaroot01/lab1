<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── conversations: indexes إضافية للفلترة والترتيب السريع ───
        Schema::table('conversations', function (Blueprint $table) {
            // composite index للفلترة بالحالة + الترتيب بآخر رسالة
            $table->index(['status', 'last_message_at'], 'conv_status_last_msg_idx');

            // index للبحث السريع بآخر رسالة مقروءة (لحساب unread)
            $table->index('admin_last_read_message_id', 'conv_admin_read_idx');
            $table->index('patient_last_read_message_id', 'conv_patient_read_idx');
        });

        // ─── messages: indexes إضافية للـ cursor pagination و unread count ───
        Schema::table('messages', function (Blueprint $table) {
            // cursor pagination: WHERE conversation_id = X AND id < cursor ORDER BY id DESC
            $table->index(['conversation_id', 'id'], 'msg_conv_id_idx');

            // unread count: WHERE conversation_id = X AND sender_id = Y AND id > Z
            $table->index(['conversation_id', 'sender_id', 'id'], 'msg_conv_sender_id_idx');

            // soft deletes filter
            $table->index('deleted_at', 'msg_deleted_at_idx');
        });

        // ─── users: index للبحث بالاسم والهاتف في قائمة المحادثات ───
        Schema::table('users', function (Blueprint $table) {
            $table->index('phone', 'users_phone_idx');
            $table->index('name', 'users_name_idx');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex('conv_status_last_msg_idx');
            $table->dropIndex('conv_admin_read_idx');
            $table->dropIndex('conv_patient_read_idx');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('msg_conv_id_idx');
            $table->dropIndex('msg_conv_sender_id_idx');
            $table->dropIndex('msg_deleted_at_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_phone_idx');
            $table->dropIndex('users_name_idx');
        });
    }
};
