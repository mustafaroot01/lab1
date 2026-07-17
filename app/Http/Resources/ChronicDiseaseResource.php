<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChronicDiseaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'disease_name'   => $this->disease_name,
            'severity'       => $this->severity ?? 'medium',
            'severity_text'  => $this->severity === 'high' ? 'عالي' : ($this->severity === 'low' ? 'منخفض' : 'متوسط'),
            'diagnosis_date' => $this->diagnosis_date ? $this->diagnosis_date->format('Y-m-d') : null,
            'notes'          => $this->notes,
            'created_at'     => $this->created_at?->format('Y-m-d'),
        ];
    }
}
