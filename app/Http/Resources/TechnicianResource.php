<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TechnicianResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'phone'             => $this->phone,
            'address'           => $this->address,
            'specialty'         => $this->specialty,
            'has_transport'     => (bool) $this->has_transport,
            'has_equipment'     => (bool) $this->has_equipment,
            'id_front_image'    => $this->formatImageUrl($this->id_front_image),
            'id_back_image'     => $this->formatImageUrl($this->id_back_image),
            'district_id_image' => $this->formatImageUrl($this->district_id_image),
            'notes'             => $this->notes,
            'status'            => $this->status,
            'total_orders_count'     => $this->total_orders_count ?? 0,
            'completed_orders_count' => $this->completed_orders_count ?? 0,
            'active_orders_count'    => $this->active_orders_count ?? 0,
            'created_at'        => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'        => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    protected function formatImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        return asset('storage/' . $cleanPath);
    }
}

