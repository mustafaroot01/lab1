<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => 'nullable|string|max:255',
            'position'    => 'required|string|max:100',
            'image'       => 'required|string',
            'link_type'   => 'nullable|in:none,internal_offer,external_url',
            'link_target' => 'nullable|string|max:1000',
            'sort_order'  => 'nullable|integer|min:1',
            'is_active'   => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'position.required' => 'مكان ظهور البنر مطلوب',
            'image.required'    => 'صورة البنر مطلوبة',
        ];
    }
}
