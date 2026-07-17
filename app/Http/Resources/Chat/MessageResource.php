<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id'       => $this->sender_id,
            'is_admin'        => $this->isFromAdmin(),
            'is_system'       => $this->isSystem(),
            'sender_name'     => $this->sender?->name,
            'body'            => $this->body,
            'attachment'      => $this->attachment_path ? [
                'url'  => $this->attachmentUrl(),
                'type' => $this->attachment_type?->value,
                'name' => $this->attachment_name,
                'size' => $this->attachment_size,
                'mime' => $this->attachment_mime,
            ] : null,
            'edited_at'  => $this->edited_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
