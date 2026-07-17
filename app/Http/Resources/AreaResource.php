<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'district_id' => $this->district_id,
            'name'        => $this->name,
            'sort_order'  => (int) $this->sort_order,
            'is_active'   => (bool) $this->is_active,
            'district'    => $this->whenLoaded('district', fn() => [
                'id'   => $this->district->id,
                'name' => $this->district->name,
            ]),
            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
