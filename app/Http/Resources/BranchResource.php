<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name_ar'          => $this->name_ar,
            'address'          => $this->address,
            'phone'            => $this->phone,
            'lat'              => $this->lat,
            'lng'              => $this->lng,
            'radius_km'        => $this->radius_km,
            'coverage_type'    => $this->coverage_type ?? 'polygon',
            'coverage_polygon' => $this->coverage_polygon ?? [],
            'is_active'        => (bool) $this->is_active,
            'opens_at'         => $this->opens_at,
            'closes_at'        => $this->closes_at,
            'working_hours'    => $this->working_hours ?? null,
            'notes'            => $this->notes,
            'districts'        => $this->whenLoaded('districts', fn() => $this->districts->map(fn($d) => [
                'id'   => $d->id,
                'name' => $d->name,
            ])),
            'distance_km'      => $this->whenNotNull($this->distance_km ?? null),
            'created_at'       => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'       => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
