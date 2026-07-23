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
            'coupon_code' => 'nullable|string|exists:coupons,code',
            // items: optional list to let the backend detect if cart has lab tests
            'items'             => 'nullable|array',
            'items.*.item_type' => 'nullable|in:test,package',
            // lat/lng: optional — required only when cart has lab tests (enforced by service layer)
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ];
    }

    public function messages(): array
    {
        return [
            'subtotal.required' => 'إجمالي السلة مطلوب للمعاينة',
            'lat.numeric'       => 'خط العرض يجب أن يكون رقماً',
            'lng.numeric'       => 'خط الطول يجب أن يكون رقماً',
        ];
    }

    /**
     * هل السلة تحتوي على تحاليل أو باقات مخبرية؟
     * (التحاليل تحتاج موقع جغرافي لفحص التغطية)
     */
    public function hasLabItems(): bool
    {
        $items = $this->input('items', []);
        foreach ($items as $item) {
            if (in_array($item['item_type'] ?? '', ['test', 'package'])) {
                return true;
            }
        }
        return false;
    }
}
