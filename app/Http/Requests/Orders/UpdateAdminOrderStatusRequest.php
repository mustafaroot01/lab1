<?php

namespace App\Http\Requests\Orders;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'        => 'required|in:' . implode(',', Order::STATUSES),
            'technician_id' => 'nullable|exists:technicians,id',
            'cancel_reason' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required'      => 'يرجى تحديد الحالة الجديدة',
            'status.in'            => 'الحالة غير صحيحة',
            'technician_id.exists' => 'الفني المختار غير موجود في سجل الفنيين',
            'cancel_reason.max'    => 'سبب الإلغاء يجب أن لا يتجاوز 500 حرف',
        ];
    }
}
