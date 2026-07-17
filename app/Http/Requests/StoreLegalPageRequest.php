<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLegalPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'     => 'required|string|max:255',
            'slug'      => 'required|string|max:100|unique:legal_pages,slug',
            'content'   => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'اسم الصفحة مطلوب',
            'slug.required'  => 'معرف الصفحة (Slug) مطلوب',
            'slug.unique'    => 'هذا المعرف مستخدم مسبقاً لصفحة أخرى',
        ];
    }
}
