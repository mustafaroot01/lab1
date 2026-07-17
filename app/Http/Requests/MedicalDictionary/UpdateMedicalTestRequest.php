<?php

namespace App\Http\Requests\MedicalDictionary;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'test_group_id'    => 'required|exists:test_groups,id',
            'sample_type_id'   => 'nullable|exists:sample_types,id',
            'tube_type_id'     => 'nullable|exists:tube_types,id',
            'name_ar'          => 'required|string|max:255',
            'name_en'          => 'nullable|string|max:255',
            'key'              => 'nullable|string|max:255',
            'sample_type'      => 'nullable|string|max:255',
            'tube_type'        => 'nullable|string|max:255',
            'fasting_required' => 'boolean',
            'result_time'      => 'nullable|string|max:255',
            'price'            => 'nullable|numeric|min:0',
            'platform_price'   => 'nullable|numeric|min:0',
            'total_price'      => 'nullable|numeric|min:0',
            'is_active'        => 'boolean',
            'description'      => 'nullable|string',
            'sort_order'       => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'test_group_id.required' => 'مجموعة التحليل مطلوبة',
            'test_group_id.exists'   => 'المجموعة المختارة غير موجودة في النظام',
            'name_ar.required'       => 'اسم التحليل باللغة العربية مطلوب',
            'price.numeric'          => 'يجب أن يكون السعر رقماً صالحاً',
        ];
    }
}
