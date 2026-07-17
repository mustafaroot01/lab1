<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LegalPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'slug'            => $this->slug,
            'content'         => $this->content,
            'is_active'       => (bool) $this->is_active,
            'last_updated_at' => ($this->last_updated_at ?: $this->updated_at)?->format('Y-m-d H:i:s'),
            'created_at'      => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
