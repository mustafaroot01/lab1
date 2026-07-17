<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone'    => 'required|string|min:10|max:15',
            'otp_code' => 'required|string|min:4|max:6',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required'    => 'رقم الهاتف مطلوب',
            'otp_code.required' => 'رمز التحقق مطلوب',
        ];
    }
}
