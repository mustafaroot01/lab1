<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Chat\Message;

class NewChatMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $messageModel;

    public $tries = 3;

    public function backoff()
    {
        return [10, 30, 60];
    }

    public function __construct(Message $messageModel)
    {
        $this->messageModel = $messageModel;
    }

    public function via($notifiable)
    {
        return [\App\Channels\FirebaseChannel::class];
    }

    public function toFirebase($notifiable)
    {
        $body = $this->messageModel->body ?? 'صورة/مرفق جديد 📎';
        
        return [
            'title' => 'رسالة جديدة من المختبر',
            'body'  => mb_substr($body, 0, 100),
            'collapse_key' => 'chat',
            'data'  => [
                'chat_id'    => (string) $this->messageModel->conversation_id,
                'patient_id' => (string) $notifiable->id,
                'message_id' => (string) $this->messageModel->id,
                'type'       => 'chat',
                'sender'     => 'admin',
            ]
        ];
    }
}
