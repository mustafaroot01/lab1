<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lab_name'              => 'nullable|string|max:255',
            'support_phone'         => 'nullable|string|max:50',
            'support_email'         => 'nullable|email|max:255',
            'work_hours'            => 'nullable|string|max:255',
            'welcome_message'       => 'nullable|string|max:1000',
            'package_offers_active' => 'nullable|boolean',
            'chat_active'           => 'nullable|boolean',
            'maintenance_mode'      => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'lab_name.max'        => 'اسم المختبر يجب ألا يتجاوز 255 حرفاً.',
            'support_email.email' => 'البريد الإلكتروني الممدخل غير صالح.',
            'welcome_message.max' => 'رسالة الترحيب طويلة جداً.',
        ];
    }
}
