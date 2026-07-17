<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RequestOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone'           => 'required|string|min:10|max:15',
            'agreed_to_terms' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required'           => 'رقم الهاتف مطلوب',
            'phone.min'                => 'رقم الهاتف يجب أن يتكون من 10 أرقام على الأقل',
            'agreed_to_terms.required' => 'يجب الموافقة على شروط الخدمة وسياسة الخصوصية للمتابعة',
        ];
    }
}
