<?php

namespace App\Http\Resources\MedicalDictionary;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TubeTypeResource extends JsonResource
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
            'cap_color'   => $this->cap_color,
            'color_hex'   => $this->color_hex,
            'additive'    => $this->additive,
            'icon'        => $this->icon,
            'description' => $this->description,
            'sort_order'  => (int) $this->sort_order,
            'tests_count' => $this->tests_count ?? $this->whenCounted('tests'),
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
