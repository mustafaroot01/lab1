<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'sometimes|required|string|min:2|max:150',
            'birth_date'  => 'nullable|date|before:today',
            'gender'      => 'nullable|in:male,female',
            'district_id' => 'nullable|exists:districts,id',
            'address'     => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'       => 'اسم المراجع مطلوب',
            'name.min'            => 'الاسم يجب أن لا يقل عن حرفين',
            'birth_date.date'     => 'تاريخ الميلاد غير صالح',
            'birth_date.before'   => 'تاريخ الميلاد يجب أن يكون في الماضي',
            'gender.in'           => 'الجنس يجب أن يكون إما ذكر (male) أو أنثى (female)',
            'district_id.exists'  => 'القضاء المختار غير موجود في قاعدة البيانات',
        ];
    }
}
