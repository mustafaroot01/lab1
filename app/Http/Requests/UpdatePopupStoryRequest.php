<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePopupStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'              => 'sometimes|required|string|max:255',
            'image'              => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'duration_seconds'   => 'sometimes|required|integer|min:3|max:30',
            'display_frequency'  => 'sometimes|required|in:always,once_per_day,once_per_session',
            'button_text'        => 'nullable|string|max:100',
            'button_link_type'   => 'sometimes|required|in:none,link,package,test,coupon,external',
            'button_link_target' => 'nullable|string|max:255',
            'start_at'           => 'nullable|date',
            'end_at'             => 'nullable|date|after_or_equal:start_at',
            'sort_order'         => 'nullable|integer',
            'is_active'          => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'            => 'عنوان الإعلان مطلوب.',
            'image.image'               => 'الملف المرفوع يجب أن يكون صورة صالحة.',
            'image.mimes'               => 'صيغ الصور المسموحة هي: jpeg, png, jpg, webp, gif.',
            'image.max'                 => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت.',
            'duration_seconds.required' => 'مدة عرض الإعلان بالثواني مطلوبة.',
            'duration_seconds.min'      => 'مدة الإعلان يجب ألا تقل عن 3 ثوانٍ.',
            'duration_seconds.max'      => 'مدة الإعلان يجب ألا تزيد عن 30 ثانية.',
            'end_at.after_or_equal'     => 'تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية.',
        ];
    }
}
