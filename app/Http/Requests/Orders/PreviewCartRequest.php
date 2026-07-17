<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class PreviewCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subtotal'    => 'required|numeric|min:0',
            'branch_id'   => 'nullable|exists:branches,id',
            'district_id' => 'nullable|exists:districts,id',
            'area_id'     => 'nullable|exists:areas,id',
            'coupon_code' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'subtotal.required'  => 'إجمالي السلة مطلوب للمعاينة',
            'branch_id.exists'   => 'الفرع المختار غير متوفر',
            'district_id.exists' => 'القضاء المختار غير متوفر',
            'area_id.exists'     => 'المنطقة المختارة غير متوفرة',
        ];
    }
}
