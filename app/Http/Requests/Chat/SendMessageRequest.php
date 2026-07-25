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
            'client_message_id' => ['nullable', 'string', 'max:255'],
            'text'              => ['nullable', 'string', 'max:5000'],
            'file'              => ['nullable', 'file', 'image', 'max:10240'], // max 10MB image
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (empty($this->text) && !$this->hasFile('file')) {
                $validator->errors()->add('message', 'يجب إرسال نص رسالة أو ملف صورة على الأقل.');
            }
        });
    }
}
