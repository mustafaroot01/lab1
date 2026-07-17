<?php

namespace App\Http\Resources\MedicalDictionary;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name_ar'     => $this->name_ar,
            'name_en'     => $this->name_en,
            'key'         => $this->key,
            'icon'        => $this->icon,
            'color'       => $this->color,
            'is_active'   => (bool) $this->is_active,
            'sort_order'  => (int) $this->sort_order,
            'tests_count' => $this->whenCounted('tests'),
            'tests'       => MedicalTestResource::collection($this->whenLoaded('tests')),
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
