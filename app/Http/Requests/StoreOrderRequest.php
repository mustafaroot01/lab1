<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // بنود السلة (على الأقل عنصر واحد)
            'items'              => 'required|array|min:1',
            'items.*.item_type' => 'required|in:test,package',
            'items.*.item_id'   => 'required|integer|min:1',

            // تفاصيل الزيارة
            'visit_date'   => 'required|date|after_or_equal:today',
            'visit_time'   => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'visit_period' => 'required|in:morning,noon,evening',

            // الموقع (أحد الخيارين إلزامي)
            'lat'          => 'required_without:address_text|nullable|numeric',
            'lng'          => 'required_without:address_text|nullable|numeric',
            'address_text' => 'required_without:lat|nullable|string|max:500',

            // اختيارية
            'coupon_code'    => 'nullable|string|max:100',
            'doctor_name'    => 'nullable|string|max:255',
            'referral_image' => 'nullable|string|max:500',  // مسار الصورة بعد الرفع
            'notes'          => 'nullable|string|max:1000',
            'branch_id'      => 'nullable|exists:branches,id',
            'district_id'    => 'nullable|exists:districts,id',
            'area_id'        => 'nullable|exists:areas,id',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'            => 'السلة فارغة، يرجى اختيار تحليل على الأقل',
            'items.min'                 => 'السلة فارغة، يرجى اختيار تحليل على الأقل',
            'items.*.item_type.in'      => 'نوع العنصر غير صحيح (test أو package)',
            'visit_date.required'       => 'يرجى اختيار تاريخ الزيارة',
            'visit_date.after_or_equal' => 'لا يمكن اختيار تاريخ في الماضي',
            'visit_time.required'       => 'يرجى اختيار وقت الزيارة',
            'visit_period.required'     => 'يرجى تحديد الفترة (صباح/ظهر/مساء)',
            'lat.required_without'      => 'يرجى تحديد الموقع أو كتابة العنوان',
            'address_text.required_without' => 'يرجى تحديد الموقع أو كتابة العنوان',
        ];
    }
}
