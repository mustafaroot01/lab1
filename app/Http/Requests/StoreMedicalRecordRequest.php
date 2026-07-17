<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type') ?: $this->route('type');

        // أتمتة تحويل صياغة الفرونت اند (الجمع ومع شريطة) إلى الصياغة المعتمدة في الباك اند
        if (in_array($type, ['chronic-diseases', 'chronic_diseases', 'chronic_disease'])) {
            $type = 'chronic_disease';
        } elseif (in_array($type, ['medications', 'medication'])) {
            $type = 'medication';
        } elseif (in_array($type, ['allergies', 'allergy'])) {
            $type = 'allergy';
        }

        $this->merge(['type' => $type]);

        if ($type === 'chronic_disease') {
            return [
                'type'           => 'required|string|in:chronic_disease,medication,allergy',
                'disease_name'   => 'required|string|max:255',
                'severity'       => 'nullable|string|in:low,medium,high',
                'diagnosis_date' => 'nullable|date',
                'notes'          => 'nullable|string|max:1000',
            ];
        }

        if ($type === 'medication') {
            return [
                'type'            => 'required|string|in:chronic_disease,medication,allergy',
                'medication_name' => 'required|string|max:255',
                'dosage'          => 'nullable|string|max:255',
                'frequency'       => 'nullable|string|max:255',
                'start_date'      => 'nullable|date',
                'notes'           => 'nullable|string|max:1000',
            ];
        }

        if ($type === 'allergy') {
            return [
                'type'     => 'required|string|in:chronic_disease,medication,allergy',
                'allergen' => 'required|string|max:255',
                'severity' => 'nullable|string|in:low,medium,high',
                'reaction' => 'nullable|string|max:255',
                'notes'    => 'nullable|string|max:1000',
            ];
        }

        return [
            'type' => 'required|string|in:chronic_disease,medication,allergy',
        ];
    }


    public function messages(): array
    {
        return [
            'disease_name.required'    => 'يرجى إدخال اسم المرض المزمن',
            'medication_name.required' => 'يرجى إدخال اسم الدواء',
            'allergen.required'        => 'يرجى إدخال اسم المادة المسببة للحساسية',
        ];
    }
}
