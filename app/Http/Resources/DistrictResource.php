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
            'branch'         => $this->whenLoaded('branch', fn() => [
                'id'             => $this->branch->id,
                'name_ar'        => $this->branch->name_ar,
                'phone'          => $this->branch->phone,
                'service_fee'    => (float) ($this->branch->service_fee ?? 0),
                'free_threshold' => (float) ($this->branch->free_threshold ?? 0),
            ]),
            'sort_order'     => (int) $this->sort_order,
            'is_active'   => (bool) $this->is_active,
            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
