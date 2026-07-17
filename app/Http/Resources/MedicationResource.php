<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'medication_name' => $this->medication_name,
            'dosage'          => $this->dosage,
            'frequency'       => $this->frequency,
            'start_date'      => $this->start_date ? $this->start_date->format('Y-m-d') : null,
            'notes'           => $this->notes,
            'created_at'      => $this->created_at?->format('Y-m-d'),
        ];
    }
}
