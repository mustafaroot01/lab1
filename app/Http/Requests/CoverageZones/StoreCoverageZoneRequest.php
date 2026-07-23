<?php

namespace App\Http\Requests\CoverageZones;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoverageZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Illuminate\Support\Facades\Log::error('Validation Failed: ', $validator->errors()->toArray());
        parent::failedValidation($validator);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'coverage_type' => 'required|in:POLYGON,RADIUS',
            'geojson' => 'nullable|required_if:coverage_type,POLYGON|array',
            'center_lat' => 'nullable|required_if:coverage_type,RADIUS|numeric',
            'center_lng' => 'nullable|required_if:coverage_type,RADIUS|numeric',
            'radius_meters' => 'nullable|required_if:coverage_type,RADIUS|integer',
            'pricing_type' => 'required|in:FIXED,RULE_BASED',
            'service_fee' => 'required|numeric|min:0',
            'free_visit_threshold' => 'nullable|numeric|min:0',
            'priority' => 'required|integer',
            'grace_distance' => 'nullable|integer|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE,MAINTENANCE',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'starts_at' => 'nullable|date_format:H:i',
            'ends_at' => 'nullable|date_format:H:i',
        ];
    }
}
