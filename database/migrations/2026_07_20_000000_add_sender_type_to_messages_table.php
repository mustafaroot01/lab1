<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // نوع المُرسِل الصريح لمنع تصادم المُعرّفات بين جدولي admins و patients
            // (لأن الـ auto-increment متداخل ولا يمكن التمييز بـ sender_id وحده)
            $table->string('sender_type')->nullable()->after('sender_id');
            $table->index(['conversation_id', 'sender_type'], 'msg_conv_sender_type_idx');
        });

        // تعبئة البيانات القديمة: الافتراض أدمن، ثم تصحيح رسائل المريض (غير النظامية)
        // التي مُرسِلها = مالك المحادثة (patient_id أو user_id)
        DB::statement("UPDATE messages SET sender_type = 'admin'");

        DB::statement("
            UPDATE messages SET sender_type = 'patient'
            WHERE is_system = 0
              AND sender_id = (
                SELECT COALESCE(c.patient_id, c.user_id)
                FROM conversations c
                WHERE c.id = messages.conversation_id
              )
        ");
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('msg_conv_sender_type_idx');
            $table->dropColumn('sender_type');
        });
    }
};
