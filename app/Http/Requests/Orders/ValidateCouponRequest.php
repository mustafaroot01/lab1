<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class ValidateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'     => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'     => 'كود الخصم مطلوب',
            'subtotal.required' => 'إجمالي مبلغ السلة مطلوب لحساب الخصم',
            'subtotal.numeric'  => 'يجب أن يكون الإجمالي صالحاً',
        ];
    }
}
