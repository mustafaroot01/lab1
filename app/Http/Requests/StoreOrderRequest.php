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

            // الموقع (إلزامي)
            'address_text' => 'required|string|max:500',

            // اختيارية
            'coupon_code'    => 'nullable|string|max:100',
            'doctor_name'    => 'nullable|string|max:255',
            'referral_image' => 'nullable|string|max:500',  // مسار الصورة بعد الرفع
            'notes'          => 'nullable|string|max:1000',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
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
            'address_text.required'     => 'يرجى كتابة العنوان التفصيلي',
            'lat.required'              => 'تحديد الموقع على الخريطة مطلوب',
            'lng.required'              => 'تحديد الموقع على الخريطة مطلوب',
        ];
    }

    public function after(): array
    {
        return [
            function (\Illuminate\Validation\Validator $validator) {
                if ($validator->errors()->any()) return;

                $date = \Carbon\Carbon::parse($this->input('visit_date'));
                $requestedTime = $this->input('visit_time');
                
                if ($date->isToday()) {
                    $requestedCarbon = clone $date;
                    $timeParts = explode(':', $requestedTime);
                    $requestedCarbon->setTime($timeParts[0], $timeParts[1]);
                    
                    if ($requestedCarbon->isPast()) {
                        $validator->errors()->add('visit_time', 'لا يمكن حجز وقت قد مضى');
                    }
                }
            }
        ];
    }
}
