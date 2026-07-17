<?php

namespace App\Http\Requests\MedicalDictionary;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar'    => 'required|string|max:255',
            'name_en'    => 'nullable|string|max:255',
            'key'        => 'required|string|unique:test_groups,key|max:255',
            'icon'       => 'nullable|string|max:255',
            'color'      => 'nullable|string|max:255',
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'name_ar.required' => 'اسم المجموعة باللغة العربية مطلوب',
            'key.required'     => 'المعرف البرمجي (key) للمجموعة مطلوب',
            'key.unique'       => 'هذا المعرف البرمجي (key) مستخدم بالفعل لمجموعة أخرى',
        ];
    }
}
