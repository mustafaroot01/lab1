<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class UploadReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'صورة الراجعة الطبية مطلوبة',
            'image.image'    => 'يجب أن يكون الملف صورة صحيحة',
            'image.mimes'    => 'امتداد الصورة يجب أن يكون: jpg, jpeg, png, أو webp',
            'image.max'      => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت',
        ];
    }
}
