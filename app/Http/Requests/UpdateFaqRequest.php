<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category'   => 'sometimes|required|string|max:100',
            'question'   => 'sometimes|required|string|max:1000',
            'answer'     => 'sometimes|required|string',
            'sort_order' => 'nullable|integer|min:1',
            'is_active'  => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'تصنيف السؤال مطلوب',
            'question.required' => 'نص السؤال مطلوب',
            'answer.required'   => 'نص الجواب مطلوب',
        ];
    }
}
