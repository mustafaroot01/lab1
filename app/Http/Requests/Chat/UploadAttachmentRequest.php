<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class UploadAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_message_id' => ['required', 'string', 'uuid'],
            'conversation_id' => ['required', 'string', 'uuid'],
            'attachment' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'], // 10MB
        ];
    }
}
