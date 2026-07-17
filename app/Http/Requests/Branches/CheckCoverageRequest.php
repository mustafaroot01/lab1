<?php

namespace App\Http\Requests\Branches;

use Illuminate\Foundation\Http\FormRequest;

class CheckCoverageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'district_id' => 'nullable|exists:districts,id',
            'lat'         => 'nullable|numeric|between:-90,90',
            'lng'         => 'nullable|numeric|between:-180,180',
        ];
    }

    public function messages(): array
    {
        return [
            'district_id.exists' => 'القضاء المختار غير موجود في النظام',
            'lat.numeric'        => 'خط العرض يجب أن يكون رقماً',
            'lat.between'        => 'خط العرض يجب أن يكون بين -90 و 90',
            'lng.numeric'        => 'خط الطول يجب أن يكون رقماً',
            'lng.between'        => 'خط الطول يجب أن يكون بين -180 و 180',
        ];
    }
}
