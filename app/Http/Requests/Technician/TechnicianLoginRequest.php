<?php

namespace App\Http\Requests\Technician;

use Illuminate\Foundation\Http\FormRequest;

class TechnicianLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone'    => 'required|string|max:20',
            'password' => 'required|string|min:4',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required'    => 'رقم الهاتف مطلوب لدخول الفني',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min'      => 'كلمة المرور يجب أن لا تقل عن 4 أحرف',
        ];
    }
}
