<?php

namespace App\Http\Requests\MedicalDictionary;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $group = $this->route('id') ?? $this->route('group');
        $groupId = is_object($group) ? $group->id : $group;

        return [
            'name_ar'    => 'required|string|max:255',
            'name_en'    => 'nullable|string|max:255',
            'key'        => 'required|string|max:255|unique:test_groups,key,' . $groupId,

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
