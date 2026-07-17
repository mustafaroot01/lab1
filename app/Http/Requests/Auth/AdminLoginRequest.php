<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => 'required|string',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'يرجى إدخال البريد الإلكتروني أو رقم الهاتف',
            'password.required' => 'يرجى إدخال كلمة المرور',
        ];
    }
}
