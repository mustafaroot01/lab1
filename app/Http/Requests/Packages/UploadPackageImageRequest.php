<?php

namespace App\Http\Requests\Packages;

use Illuminate\Foundation\Http\FormRequest;

class UploadPackageImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'يرجى اختيار صورة لرفعها',
            'image.image'    => 'الملف المختار يجب أن يكون صورة',
            'image.mimes'    => 'صيغة الصورة غير مدعومة (مدعوم: jpeg, png, jpg, gif, webp, svg)',
            'image.max'      => 'حجم الصورة يجب أن لا يتجاوز 5 ميغابايت',
        ];
    }
}
