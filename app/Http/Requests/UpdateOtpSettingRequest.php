<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOtpSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'otp_provider'  => 'nullable|string|in:otpiq,arqam',
            'otpiq_api_key' => 'nullable|string|max:500',
            'arqam_api_key' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'otp_provider.in'      => 'المزود المختار يجب أن يكون إما OTPIQ أو Arqam Tech',
            'otpiq_api_key.string' => 'مفتاح OTPIQ يجب أن يكون نصاً صالحاً',
            'arqam_api_key.string' => 'مفتاح Arqam يجب أن يكون نصاً صالحاً',
        ];
    }
}
