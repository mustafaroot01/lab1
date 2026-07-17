<?php

namespace App\Events\Chat;

use App\Models\Chat\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public bool $byAdmin = true
    ) {
    }

    /**
     * القنوات التي سيتم بث الحدث عليها.
     */
    public function broadcastOn(): array
    {
        $patientId = $this->conversation->patient_id ?: $this->conversation->user_id;
        return [
            new PrivateChannel('private-conversation.' . $patientId),
            new PrivateChannel('private-admin-chat'),
        ];
    }

    /**
     * اسم الحدث كما سيستقبله Echo في الفرونت إند.
     */
    public function broadcastAs(): string
    {
        return 'ConversationRead';
    }

    /**
     * البيانات المرسلة مع الحدث.
     */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'admin_last_read_message_id' => $this->conversation->admin_last_read_message_id,
            'patient_last_read_message_id' => $this->conversation->patient_last_read_message_id,
            'by_admin' => $this->byAdmin,
        ];
    }
}
