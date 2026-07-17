<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class UploadOrderResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:20480', // 20 MB max
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'يرجى اختيار ملف أو صورة لرفعه',
            'file.mimes'    => 'يجب أن يكون الملف بصيغة PDF أو صورة (jpg, png)',
            'file.max'      => 'حجم الملف يجب أن لا يتجاوز 20 ميغابايت',
        ];
    }
}
