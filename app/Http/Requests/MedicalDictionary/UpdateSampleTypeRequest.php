<?php

namespace App\Http\Requests\MedicalDictionary;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSampleTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sample = $this->route('id') ?? $this->route('sampleType');
        $sampleId = is_object($sample) ? $sample->id : $sample;

        return [
            'name_ar'     => 'required|string|max:255|unique:sample_types,name_ar,' . $sampleId,

            'name_en'     => 'nullable|string|max:255',
            'code'        => 'nullable|string|max:255',
            'icon'        => 'nullable|string|max:255',
            'color'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'name_ar.required' => 'اسم نوع العينة باللغة العربية مطلوب',
            'name_ar.unique'   => 'نوع العينة بهذا الاسم مسجل مسبقاً',
        ];
    }
}
