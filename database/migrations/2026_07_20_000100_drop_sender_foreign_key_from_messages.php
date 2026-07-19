<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * يزيل قيد المفتاح الأجنبي messages.sender_id -> users(id).
     *
     * السبب: المُرسِل أصبح إمّا Admin أو Patient (جدولان منفصلان بأرقام متداخلة)،
     * والتمييز يتم عبر العمود الصريح sender_type. الإبقاء على القيد نحو users
     * يكسر رسائل أي مريض/أدمن جديد لا يملك صفاً مطابقاً في جدول users.
     * كذلك تُزال قيود conversations التي تشير إلى users لكنها تحمل معرّفات admin/patient.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildMessagesTableSqlite();

            // conversations على SQLite أُعيد بناؤها سابقاً بلا أي قيود FK — لا شيء لإزالته.
            return;
        }

        // ─── MySQL / غيرها ───────────────────────────────────────────────
        $this->dropForeignSafely('messages', 'sender_id');

        foreach (['user_id', 'last_sender_id', 'closed_by', 'assigned_to_user_id'] as $column) {
            $this->dropForeignSafely('conversations', $column);
        }
    }

    public function down(): void
    {
        // لا حاجة لإعادة القيد — التصميم الجديد يعتمد على sender_type.
    }

    private function dropForeignSafely(string $table, string $column): void
    {
        try {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->dropForeign([$column]);
            });
        } catch (\Throwable $e) {
            // القيد غير موجود مسبقاً — نتجاهل بأمان.
        }
    }

    private function rebuildMessagesTableSqlite(): void
    {
        $columns = 'id, conversation_id, sender_id, body, attachment_disk, attachment_path, '
            . 'attachment_mime, attachment_type, attachment_name, attachment_size, edited_at, '
            . 'created_at, updated_at, deleted_at, is_system, sender_type';

        DB::statement('PRAGMA foreign_keys = OFF;');

        DB::statement('ALTER TABLE messages RENAME TO messages_old;');

        // نفس السكيمة لكن مع الإبقاء على قيد conversation_id فقط وحذف قيد sender_id
        DB::statement('CREATE TABLE "messages" (
            "id" integer primary key autoincrement not null,
            "conversation_id" integer not null,
            "sender_id" integer not null,
            "body" text,
            "attachment_disk" varchar,
            "attachment_path" varchar,
            "attachment_mime" varchar,
            "attachment_type" varchar,
            "attachment_name" varchar,
            "attachment_size" integer,
            "edited_at" datetime,
            "created_at" datetime,
            "updated_at" datetime,
            "deleted_at" datetime,
            "is_system" tinyint(1) not null default \'0\',
            "sender_type" varchar,
            foreign key("conversation_id") references "conversations"("id") on delete cascade
        );');

        DB::statement("INSERT INTO messages ($columns) SELECT $columns FROM messages_old;");

        DB::statement('DROP TABLE messages_old;');

        // إعادة بناء كل الفهارس
        DB::statement('CREATE INDEX "messages_conversation_id_created_at_index" ON "messages" ("conversation_id", "created_at");');
        DB::statement('CREATE INDEX "msg_conv_id_idx" ON "messages" ("conversation_id", "id");');
        DB::statement('CREATE INDEX "msg_conv_sender_id_idx" ON "messages" ("conversation_id", "sender_id", "id");');
        DB::statement('CREATE INDEX "msg_deleted_at_idx" ON "messages" ("deleted_at");');
        DB::statement('CREATE INDEX "msg_is_system_idx" ON "messages" ("is_system");');
        DB::statement('CREATE INDEX "msg_conv_sender_type_idx" ON "messages" ("conversation_id", "sender_type");');

        DB::statement('PRAGMA foreign_keys = ON;');
    }
};
