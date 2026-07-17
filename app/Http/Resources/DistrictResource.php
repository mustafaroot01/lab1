<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistrictResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'governorate' => $this->governorate,
            'branch_id'      => $this->branch_id,
            'service_fee'    => $this->service_fee !== null ? (float) $this->service_fee : null,
            'free_threshold' => $this->free_threshold !== null ? (float) $this->free_threshold : null,
            'branch'         => $this->whenLoaded('branch', fn() => [
                'id'             => $this->branch->id,
                'name_ar'        => $this->branch->name_ar,
                'phone'          => $this->branch->phone,
                'service_fee'    => (float) ($this->branch->service_fee ?? 0),
                'free_threshold' => (float) ($this->branch->free_threshold ?? 0),
            ]),
            'sort_order'     => (int) $this->sort_order,
            'is_active'   => (bool) $this->is_active,
            'areas_count' => $this->whenCounted('areas'),
            'areas'       => AreaResource::collection($this->whenLoaded('areas')),
            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
