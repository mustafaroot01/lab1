<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar'          => 'required|string|max:255',
            'address'          => 'nullable|string|max:500',
            'phone'            => 'nullable|string|max:20',
            'lat'              => 'nullable|numeric|between:-90,90',
            'lng'              => 'nullable|numeric|between:-180,180',
            'radius_km'        => 'nullable|numeric|min:0.5|max:100',
            'coverage_type'    => 'nullable|in:circle,polygon',
            'coverage_polygon' => 'nullable|array',
            'is_active'        => 'boolean',
            'opens_at'         => 'nullable',
            'closes_at'        => 'nullable',
            'working_hours'    => 'nullable|array',
            'notes'            => 'nullable|string',
            'district_ids'     => 'nullable|array',
            'district_ids.*'   => 'exists:districts,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name_ar.required' => 'اسم الفرع مطلوب',
            'lat.between'      => 'خط العرض يجب أن يكون بين -90 و 90',
            'lng.between'      => 'خط الطول يجب أن يكون بين -180 و 180',
            'radius_km.min'    => 'نطاق الخدمة يجب أن يكون على الأقل 0.5 كيلومتر',
            'radius_km.max'    => 'نطاق الخدمة لا يمكن أن يتجاوز 100 كيلومتر',
        ];
    }
}
