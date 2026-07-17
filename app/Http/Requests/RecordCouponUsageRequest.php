<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordCouponUsageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'total_before_discount' => 'required|numeric|min:0',
        ];
    }
}
