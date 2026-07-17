<?php

namespace App\Http\Requests\MedicalDictionary;

use Illuminate\Foundation\Http\FormRequest;

class StoreTubeTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar'     => 'required|string|max:255|unique:tube_types,name_ar',
            'name_en'     => 'nullable|string|max:255',
            'code'        => 'nullable|string|max:255',
            'cap_color'   => 'nullable|string|max:255',
            'color_hex'   => 'nullable|string|max:255',
            'additive'    => 'nullable|string|max:255',
            'icon'        => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'name_ar.required' => 'اسم الأنبوب باللغة العربية مطلوب',
            'name_ar.unique'   => 'أنبوب بهذا الاسم مسجل مسبقاً',
        ];
    }
}
