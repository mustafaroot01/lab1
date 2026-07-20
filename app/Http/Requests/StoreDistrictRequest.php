<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDistrictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'governorate'    => 'nullable|string|max:255',
            'branch_id'      => 'nullable|exists:branches,id',
            'sort_order'     => 'nullable|integer|min:1',
            'is_active'      => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'اسم القضاء مطلوب',
            'branch_id.exists' => 'الفرع المخبري المحدد غير موجود',
        ];
    }
}
