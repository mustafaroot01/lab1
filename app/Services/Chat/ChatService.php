<?php

namespace App\Services\Chat;

use App\Enums\Chat\AttachmentType;
use App\Enums\Chat\ConversationStatus;
use App\Events\Chat\ConversationAssigned;
use App\Events\Chat\ConversationRead;
use App\Events\Chat\ConversationStatusChanged;
use App\Events\Chat\MessageCreated;
use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChatService
{
    private const ATTACHMENT_DISK = 'public';

    /**
     * إرسال رسالة (نص و/أو مرفق) داخل محادثة من طرف مُرسِل محدد.
     */
    public function sendMessage(Conversation $conversation, $sender, ?string $body, ?UploadedFile $attachment, bool $isSystem = false): Message
    {
        // إذا كان المرسل أدمن ولم تكن المحادثة مستلمة أو مستلمة لشخص آخر ولم تكن الرسالة رسالة نظام بالفعل
        if (!$isSystem && ($sender instanceof \App\Models\Admin || ($sender->role ?? null) === 'admin') && !$conversation->isAssignedTo($sender)) {
            $this->claim($conversation, $sender);
        }

        // تخزين المرفق خارج الـ transaction (عملية I/O بطيئة)
        $attachmentData = $attachment ? $this->storeAttachment($conversation, $attachment) : [];

        $message = DB::transaction(function () use ($conversation, $sender, $body, $isSystem, $attachment, $attachmentData) {
            $message = Message::create(array_merge([
                'conversation_id' => $conversation->id,
                'sender_id'       => $sender->id,
                'is_system'       => $isSystem,
                'body'            => $body,
            ], $attachmentData));

            $conversation->update([
                'last_message_at'      => now(),
                'last_sender_id'       => $sender->id,
                'last_message_preview' => $isSystem ? '✨ انضم المشرف للمحادثة ✨' : ($body ? mb_substr($body, 0, 120) : ($attachment ? 'مرفق' : null)),
            ]);

            return $message;
        });

        broadcast(new MessageCreated($message));

        return $message;
    }

    /**
     * استلام المشرف للمحادثة (Claim / Assign) وإصدار رسالة نظام آلية.
     * محمي بـ lock لمنع استلام أدمنين بنفس اللحظة.
     */
    public function claim(Conversation $conversation, $admin): Conversation
    {
        $claimed = DB::transaction(function () use ($conversation, $admin) {
            // lock السجل لمنع race condition بين أدمنين
            $locked = Conversation::whereKey($conversation->id)->lockForUpdate()->first();

            if ($locked->isAssignedTo($admin)) {
                return false; // مستلمة مسبقاً لنفس الأدمن
            }

            $locked->update([
                'assigned_to_user_id' => $admin->id,
                'assigned_at'         => now(),
            ]);

            $conversation->refresh();

            return true;
        });

        if ($claimed) {
            // إدراج رسالة النظام التلقائية لإعلام المريض بانضمام المشرف (تقوم بالبث أيضاً)
            $this->sendMessage(
                $conversation,
                $admin,
                "✨ انضم المشرف [{$admin->name}] إلى المحادثة وهو جاهز لمساعدتك الآن ✨",
                null,
                true
            );

            broadcast(new ConversationAssigned($conversation));
        }

        return $conversation;
    }

    private function storeAttachment(Conversation $conversation, UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->store("chat/{$conversation->id}", self::ATTACHMENT_DISK);

        return [
            'attachment_disk'  => self::ATTACHMENT_DISK,
            'attachment_path'  => $path,
            'attachment_mime'  => $file->getClientMimeType(),
            'attachment_type'  => AttachmentType::fromExtension($extension)->value,
            'attachment_name'  => $file->getClientOriginalName(),
            'attachment_size'  => $file->getSize(),
        ];
    }

    public function markReadByAdmin(Conversation $conversation): void
    {
        $lastId = $conversation->messages()->max('id');
        if ($lastId && $lastId != $conversation->admin_last_read_message_id) {
            $conversation->update(['admin_last_read_message_id' => $lastId]);
            broadcast(new ConversationRead($conversation, true));
        }
    }

    public function markReadByPatient(Conversation $conversation): void
    {
        $lastId = $conversation->messages()->max('id');
        if ($lastId && $lastId != $conversation->patient_last_read_message_id) {
            $conversation->update(['patient_last_read_message_id' => $lastId]);
            broadcast(new ConversationRead($conversation, false));
        }
    }

    public function close(Conversation $conversation, $admin): Conversation
    {
        $conversation->update([
            'status'    => ConversationStatus::Closed->value,
            'closed_at' => now(),
            'closed_by' => $admin->id,
        ]);

        broadcast(new ConversationStatusChanged($conversation));

        return $conversation;
    }

    public function reopen(Conversation $conversation): Conversation
    {
        $conversation->update([
            'status'    => ConversationStatus::Open->value,
            'closed_at' => null,
            'closed_by' => null,
        ]);

        broadcast(new ConversationStatusChanged($conversation));

        return $conversation;
    }
}
