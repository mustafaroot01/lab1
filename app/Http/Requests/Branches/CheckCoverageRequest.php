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
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ];
    }

    public function messages(): array
    {
        return [
            'lat.required' => 'خط العرض (lat) مطلوب',
            'lat.numeric'  => 'خط العرض يجب أن يكون رقماً',
            'lat.between'  => 'خط العرض يجب أن يكون بين -90 و 90',
            'lng.required' => 'خط الطول (lng) مطلوب',
            'lng.numeric'  => 'خط الطول يجب أن يكون رقماً',
            'lng.between'  => 'خط الطول يجب أن يكون بين -180 و 180',
        ];
    }
}
