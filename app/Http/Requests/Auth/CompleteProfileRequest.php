<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class CompleteProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|min:2|max:150',
            'birth_date'  => 'required|date|before:today',
            'gender'      => 'required|in:male,female',
            'district_id'  => 'required|exists:districts,id',
            'address' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'الاسم مطلوب',
            'birth_date.required'  => 'تاريخ الميلاد مطلوب',
            'birth_date.before'    => 'تاريخ الميلاد يجب أن يكون في الماضي',
            'gender.required'      => 'الجنس مطلوب',
            'district_id.required'  => 'يرجى اختيار القضاء',
            'district_id.exists'    => 'القضاء المختار غير متوفر',
            'address.required' => 'يرجى إدخال العنوان التفصيلي',
        ];
    }
}
