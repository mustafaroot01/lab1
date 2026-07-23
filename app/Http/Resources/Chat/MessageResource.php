<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Note: Data comes as an array from Supabase REST API, not an Eloquent Model.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this['id'] ?? null,
            'client_message_id' => $this['client_message_id'] ?? null,
            'conversation_id'   => $this['conversation_id'] ?? null,
            'sender_type'       => $this['sender_type'] ?? null,
            'sender_id'         => $this['sender_id'] ?? null,
            'message_type'      => $this['message_type'] ?? 'TEXT',
            'text'              => $this['text'] ?? null,
            'attachment_url'    => $this['attachment_url'] ?? null,
            'metadata'          => $this['metadata'] ?? null,
            'status'            => $this['status'] ?? 'SENT',
            'created_at'        => $this['created_at'] ?? null,
        ];
    }
}
