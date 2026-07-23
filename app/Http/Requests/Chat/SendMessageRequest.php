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
            'client_message_id' => ['required', 'string', 'uuid'],
            'conversation_id' => ['required', 'string', 'uuid'],
            'text' => ['required', 'string', 'max:5000'],
        ];
    }
}
