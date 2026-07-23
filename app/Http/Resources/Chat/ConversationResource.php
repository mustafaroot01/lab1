<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Note: Data comes as an array from Supabase REST API, not an Eloquent Model.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this['id'] ?? null,
            'status'          => $this['status'] ?? 'OPEN',
            'last_message'    => $this['last_message'] ?? null,
            'last_message_at' => $this['last_message_at'] ?? null,
            'last_sender_id'  => $this['last_sender_id'] ?? null,
            'created_at'      => $this['created_at'] ?? null,
            'updated_at'      => $this['updated_at'] ?? null,
        ];
    }
}
