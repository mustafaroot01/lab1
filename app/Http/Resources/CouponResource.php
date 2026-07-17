<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'discount_type' => $this->discount_type,
            'discount_value' => (float) $this->discount_value,
            'start_date' => $this->start_date ? $this->start_date->format('Y-m-d H:i:s') : null,
            'end_date' => $this->end_date ? $this->end_date->format('Y-m-d H:i:s') : null,
            'usage_limit' => $this->usage_limit !== null ? (int) $this->usage_limit : null,
            'used_count' => (int) $this->used_count,
            'is_active' => (bool) $this->is_active,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'usages' => CouponUsageResource::collection($this->whenLoaded('usages')),
        ];
    }
}
