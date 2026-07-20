<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'phone'                => $this->phone,
            'email'                => $this->email,
            'birth_date'           => $this->birth_date?->format('Y-m-d'),
            'gender'               => $this->gender,
            'is_profile_completed' => (bool) $this->is_profile_completed,
            'agreed_to_terms'      => (bool) $this->agreed_to_terms,
            'district_id'          => $this->district_id,
            'address'              => $this->address,
            'district'             => $this->whenLoaded('district', fn() => [
                'id'          => $this->district->id,
                'name'        => $this->district->name,
                'governorate' => $this->district->governorate,
                'branch_id'   => $this->district->branch_id,
            ]),
            'assigned_branch'      => $this->whenLoaded('district', fn() => $this->district?->branch ? [
                'id'             => $this->district->branch->id,
                'name_ar'        => $this->district->branch->name_ar,
                'phone'          => $this->district->branch->phone,
                'service_fee'    => (float) ($this->district->branch->service_fee ?? 0),
                'free_threshold' => (float) ($this->district->branch->free_threshold ?? 0),
            ] : null),
            'created_at'           => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
