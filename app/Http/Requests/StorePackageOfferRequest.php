<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'original_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'image' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
            'tests' => 'nullable|array',
            'tests.*' => 'exists:medical_tests,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name_ar.required' => 'اسم الباقة أو العرض باللغة العربية مطلوب.',
            'original_price.required' => 'سعر العرض الأصلي مطلوب.',
            'original_price.numeric' => 'يجب أن يكون السعر رقماً صحيحاً أو عشرياً.',
            'discount_price.numeric' => 'يجب أن يكون سعر الخصم رقماً صحيحاً أو عشرياً.',
            'tests.*.exists' => 'أحد التحاليل المحددة غير موجود في قاعدة البيانات.',
        ];
    }
}
