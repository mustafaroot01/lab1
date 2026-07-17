<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllergyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'allergen'      => $this->allergen,
            'severity'      => $this->severity ?? 'medium',
            'severity_text' => $this->severity === 'high' ? 'عالي' : ($this->severity === 'low' ? 'منخفض' : 'متوسط'),
            'reaction'      => $this->reaction,
            'notes'         => $this->notes,
            'created_at'    => $this->created_at?->format('Y-m-d'),
        ];
    }
}
