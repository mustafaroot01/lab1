<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class CancelOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cancel_reason' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'cancel_reason.max' => 'سبب الإلغاء يجب ألا يتجاوز 500 حرف',
        ];
    }
}
