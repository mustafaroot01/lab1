<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneralSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'lab_name'              => (string) ($this->resource['lab_name'] ?? 'Healthy Lab'),
            'support_phone'         => (string) ($this->resource['support_phone'] ?? ''),
            'support_email'         => (string) ($this->resource['support_email'] ?? ''),
            'work_hours'            => (string) ($this->resource['work_hours'] ?? ''),
            'welcome_message'       => (string) ($this->resource['welcome_message'] ?? ''),
            'package_offers_active' => filter_var($this->resource['package_offers_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'chat_active'           => filter_var($this->resource['chat_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'maintenance_mode'      => filter_var($this->resource['maintenance_mode'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }
}
