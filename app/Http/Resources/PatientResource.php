<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Sub-resources للسجل الطبي والدوائي للمريض
use App\Http\Resources\ChronicDiseaseResource;
use App\Http\Resources\MedicationResource;
use App\Http\Resources\AllergyResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name ?? 'غير محدد',
            'phone'                => $this->phone,
            'email'                => $this->email,
            'birth_date'           => $this->birth_date?->format('Y-m-d'),
            'age'                  => $this->birth_date ? $this->birth_date->age . ' سنة' : '—',
            'gender'               => $this->gender,
            'gender_text'          => $this->gender === 'male' ? 'ذكر' : ($this->gender === 'female' ? 'أنثى' : 'غير محدد'),
            'is_profile_completed' => (bool) $this->is_profile_completed,
            'is_active'            => !isset($this->is_active) || (bool) $this->is_active,
            'district_id'          => $this->district_id,
            'district'             => $this->whenLoaded('district', fn() => [
                'id'          => $this->district->id,
                'name'        => $this->district->name,
                'governorate' => $this->district->governorate,
            ]),
            'assigned_branch'      => $this->whenLoaded('district', fn() => $this->district?->branch ? [
                'id'             => $this->district->branch->id,
                'name_ar'        => $this->district->branch->name_ar,
                'phone'          => $this->district->branch->phone,
                'service_fee'    => (float) ($this->district->branch->service_fee ?? 0),
                'free_threshold' => (float) ($this->district->branch->free_threshold ?? 0),
            ] : null),
            'chronic_diseases'     => $this->whenLoaded('chronicDiseases', fn() => ChronicDiseaseResource::collection($this->chronicDiseases), []),
            'medications'          => $this->whenLoaded('medications', fn() => MedicationResource::collection($this->medications), []),
            'allergies'            => $this->whenLoaded('allergies', fn() => AllergyResource::collection($this->allergies), []),
            'created_at'           => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
