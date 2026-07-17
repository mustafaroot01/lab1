<?php

namespace App\Http\Requests\Technician;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTechnicianOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:on_the_way,sample_collected',
            'notes'  => 'nullable|string|max:1000',
            'lat'    => 'nullable|numeric|between:-90,90',
            'lng'    => 'nullable|numeric|between:-180,180',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'حالة الزيارة مطلوبة',
            'status.in'       => 'الحالة المسموحة للفني هي فقط: في الطريق (on_the_way) أو تم سحب العينة (sample_collected)',
            'lat.numeric'     => 'خط العرض يجب أن يكون قيمة رقمية صحيحة',
            'lng.numeric'     => 'خط الطول يجب أن يكون قيمة رقمية صحيحة',
        ];
    }
}
