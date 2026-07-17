<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:100|unique:coupons,code',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ];
    }
}
