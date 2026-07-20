<?php

namespace App\Events\Chat;

use App\Models\Chat\Message;
use App\Http\Resources\Chat\MessageResource;
use App\Http\Resources\Chat\ConversationResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {
    }

    /**
     * القنوات التي سيتم بث الحدث عليها.
     */
    public function broadcastOn(): array
    {
        $patientId = $this->message->conversation->patient_id ?: $this->message->conversation->user_id;
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
        return 'MessageCreated';
    }

    /**
     * البيانات المرسلة مع الحدث.
     */
    public function broadcastWith(): array
    {
        $this->message->loadMissing(['senderAdmin:id,name,role', 'senderPatient:id,name', 'conversation']);
        $this->message->conversation->loadMissing(['patient:id,name,phone,district_id', 'patient.district:id,name', 'assignedTo:id,name']);

        return [
            'message'      => (new MessageResource($this->message))->resolve(),
            'conversation' => (new ConversationResource($this->message->conversation))->resolve(),
        ];
    }
}
