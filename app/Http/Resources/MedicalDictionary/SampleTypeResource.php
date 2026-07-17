<?php

namespace App\Http\Resources\MedicalDictionary;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SampleTypeResource extends JsonResource
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
            'code'        => $this->code,
            'icon'        => $this->icon,
            'color'       => $this->color,
            'description' => $this->description,
            'sort_order'  => (int) $this->sort_order,
            'tests_count' => $this->tests_count ?? $this->whenCounted('tests'),
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
