<?php

namespace App\Events\Chat;

use App\Models\Chat\Conversation;
use App\Http\Resources\Chat\ConversationResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationAssigned implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Conversation $conversation)
    {
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
        return 'ConversationAssigned';
    }

    /**
     * البيانات المرسلة مع الحدث.
     */
    public function broadcastWith(): array
    {
        $this->conversation->loadMissing(['patient:id,name,phone,district_id,area_id', 'patient.district:id,name_ar', 'patient.area:id,name_ar', 'assignedTo:id,name']);

        return [
            'conversation' => (new ConversationResource($this->conversation))->resolve(),
        ];
    }
}
