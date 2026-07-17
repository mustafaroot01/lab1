<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'district_id' => 'required|exists:districts,id',
            'name'        => 'required|string|max:255',
            'sort_order'  => 'nullable|integer|min:1',
            'is_active'   => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'district_id.required' => 'القضاء التابع له المنطقة مطلوب',
            'district_id.exists'   => 'القضاء المحدد غير موجود',
            'name.required'        => 'اسم المنطقة مطلوب',
        ];
    }
}
