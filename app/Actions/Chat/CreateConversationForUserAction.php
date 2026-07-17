<?php

namespace App\Actions\Chat;

use App\Enums\Chat\ConversationStatus;
use App\Models\Chat\Conversation;
use App\Models\Patient;

/**
 * ينشئ محادثة للمريض إن لم تكن موجودة (تُستدعى وقت إكمال التسجيل، ومحتفظ بها هنا
 * كـ Action قابل لإعادة الاستخدام لضمان عدم وجود مريض بلا محادثة - مثلاً للمستخدمين القدامى).
 */
class CreateConversationForUserAction
{
    public function execute(Patient $user): Conversation
    {
        return Conversation::firstOrCreate(
            ['patient_id' => $user->id],
            [
                'user_id' => $user->id,
                'status'  => ConversationStatus::Open->value
            ],
        );
    }
}
