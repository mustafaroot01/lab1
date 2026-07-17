<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'district_id' => 'sometimes|required|exists:districts,id',
            'name'        => 'sometimes|required|string|max:255',
            'sort_order'  => 'nullable|integer|min:1',
            'is_active'   => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'district_id.required' => 'القضاء التابع له المنطقة مطلوب',
            'name.required'        => 'اسم المنطقة مطلوب',
        ];
    }
}
