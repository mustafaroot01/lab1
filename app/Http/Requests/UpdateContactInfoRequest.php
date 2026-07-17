<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel_type' => 'sometimes|required|string|max:50',
            'title'        => 'sometimes|required|string|max:255',
            'value'        => 'sometimes|required|string',
            'sort_order'   => 'nullable|integer|min:1',
            'is_active'    => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'channel_type.required' => 'نوع وسيلة التواصل مطلوب',
            'title.required'        => 'عنوان القناة أو الوصف مطلوب',
            'value.required'        => 'القيمة (رقم الهاتف / الرابط / العنوان) مطلوبة',
        ];
    }
}
