<?php

namespace App\Http\Resources\MedicalDictionary;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalTestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'test_group_id'    => $this->test_group_id,
            'sample_type_id'   => $this->sample_type_id,
            'tube_type_id'     => $this->tube_type_id,
            'name_ar'          => $this->name_ar,
            'name_en'          => $this->name_en,
            'key'              => $this->key,
            'sample_type'      => $this->sample_type,
            'tube_type'        => $this->tube_type,
            'fasting_required' => (bool) $this->fasting_required,
            'result_time'      => $this->result_time,
            'price'            => (float) $this->price,
            'platform_price'   => (float) $this->platform_price,
            'total_price'      => (float) $this->total_price,
            'is_active'        => (bool) $this->is_active,
            'description'      => $this->description,
            'sort_order'       => (int) $this->sort_order,
            'group'            => new TestGroupResource($this->whenLoaded('group')),
            'sample_type_obj'  => new SampleTypeResource($this->whenLoaded('sampleTypeObj')),
            'tube_type_obj'    => new TubeTypeResource($this->whenLoaded('tubeTypeObj')),
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
