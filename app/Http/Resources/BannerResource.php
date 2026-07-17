<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'position'    => $this->position,
            'image_url'   => $this->image ? (
                str_starts_with($this->image, 'data:') || str_starts_with($this->image, 'http')
                    ? $this->image
                    : (str_contains($this->image, '/storage/')
                        ? asset('storage/' . ltrim(explode('/storage/', $this->image)[1], '/'))
                        : asset('storage/' . ltrim($this->image, '/')))
            ) : null,
            'link_type'   => $this->link_type ?? 'none',
            'link_target' => $this->link_target,
            'sort_order'  => (int) $this->sort_order,
            'is_active'   => (bool) $this->is_active,
            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'  => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
