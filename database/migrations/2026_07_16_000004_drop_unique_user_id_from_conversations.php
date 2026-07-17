<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            DB::statement('CREATE TABLE conversations_temp AS SELECT * FROM conversations;');
            DB::statement('DROP TABLE conversations;');
            DB::statement('CREATE TABLE conversations ("id" integer primary key autoincrement not null, "user_id" integer, "status" varchar not null default (\'open\'), "closed_at" datetime, "closed_by" integer, "last_message_at" datetime, "last_sender_id" integer, "last_message_preview" varchar, "admin_last_read_message_id" integer, "patient_last_read_message_id" integer, "created_at" datetime, "updated_at" datetime, "assigned_to_user_id" integer, "assigned_at" datetime, "patient_id" integer);');
            DB::statement('INSERT INTO conversations SELECT * FROM conversations_temp;');
            DB::statement('DROP TABLE conversations_temp;');
            DB::statement('CREATE INDEX conversations_status_index ON conversations (status);');
            DB::statement('CREATE INDEX conversations_last_message_at_index ON conversations (last_message_at);');
            DB::statement('CREATE INDEX conv_status_last_msg_idx ON conversations (status, last_message_at);');
            DB::statement('CREATE INDEX conversations_user_id_index ON conversations (user_id);');
            DB::statement('CREATE INDEX conversations_patient_id_index ON conversations (patient_id);');
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            // 1. محاولة حذف المفتاح الأجنبي أولاً لفك قيد الفهرس الفريد
            try {
                Schema::table('conversations', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                });
            } catch (\Throwable $e) {}

            try {
                Schema::table('conversations', function (Blueprint $table) {
                    $table->dropForeign('conversations_user_id_foreign');
                });
            } catch (\Throwable $e) {}

            // 2. محاولة حذف الفهرس الفريد (Unique Index)
            try {
                Schema::table('conversations', function (Blueprint $table) {
                    $table->dropUnique(['user_id']);
                });
            } catch (\Throwable $e) {}

            try {
                Schema::table('conversations', function (Blueprint $table) {
                    $table->dropUnique('conversations_user_id_unique');
                });
            } catch (\Throwable $e) {}

            // 3. إضافة الفهرس العادي للعمود user_id
            try {
                Schema::table('conversations', function (Blueprint $table) {
                    $table->index('user_id');
                });
            } catch (\Throwable $e) {}

            // 4. إضافة الفهرس للعمود patient_id إن وجد
            if (Schema::hasColumn('conversations', 'patient_id')) {
                try {
                    Schema::table('conversations', function (Blueprint $table) {
                        $table->index('patient_id');
                    });
                } catch (\Throwable $e) {}
            }
        }
    }

    public function down(): void
    {
    }
};
