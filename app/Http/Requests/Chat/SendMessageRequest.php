<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body'       => 'nullable|string|max:5000|required_without:attachment',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:20480', // 20MB
        ];
    }

    public function messages(): array
    {
        return [
            'body.required_without' => 'يجب إرسال نص أو مرفق على الأقل',
            'attachment.mimes'       => 'الملفات المسموحة: صور (jpg, jpeg, png) أو PDF فقط',
            'attachment.max'         => 'الحد الأقصى لحجم الملف هو 20 ميجابايت',
        ];
    }
}
