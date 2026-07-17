<?php

namespace App\Http\Requests\MedicalDictionary;

use Illuminate\Foundation\Http\FormRequest;

class ToggleStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_active' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'is_active.required' => 'حالة التفعيل (is_active) مطلوبة',
            'is_active.boolean'  => 'قيمة التفعيل يجب أن تكون true أو false',
        ];
    }
}
