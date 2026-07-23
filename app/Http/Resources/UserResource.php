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
            'address'              => $this->address,
            'created_at'           => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
