<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactInfoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'channel_type' => $this->channel_type,
            'title'        => $this->title,
            'value'        => $this->value,
            'sort_order'   => (int) $this->sort_order,
            'is_active'    => (bool) $this->is_active,
            'created_at'   => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'   => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
