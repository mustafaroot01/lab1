<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLegalPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pageParam = $this->route('legal_page');
        $pageId = is_object($pageParam) ? $pageParam->id : $pageParam;

        return [
            'title'     => 'sometimes|required|string|max:255',
            'slug'      => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('legal_pages', 'slug')->ignore($pageId),
            ],
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
