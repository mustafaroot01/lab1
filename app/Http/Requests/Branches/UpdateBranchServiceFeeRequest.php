<?php

namespace App\Http\Requests\Branches;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBranchServiceFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_fee'    => 'nullable|numeric|min:0',
            'free_threshold' => 'nullable|numeric|min:0',
            'fee_notes'      => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'service_fee.numeric'    => 'رسوم الخدمة يجب أن تكون رقماً',
            'service_fee.min'        => 'رسوم الخدمة يجب أن لا تقل عن 0',
            'free_threshold.numeric' => 'الحد المجاني يجب أن يكون رقماً',
            'free_threshold.min'     => 'الحد المجاني يجب أن لا يقل عن 0',
            'fee_notes.max'          => 'ملاحظات الرسوم يجب أن لا تتجاوز 500 حرف',
        ];
    }
}
