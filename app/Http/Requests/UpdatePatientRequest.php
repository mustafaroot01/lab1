<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $patientParam = $this->route('patient');
        $userId = is_object($patientParam) ? $patientParam->id : $patientParam;

        return [
            'name'        => 'required|string|min:2|max:150',
            'phone'       => 'required|string|max:25|unique:patients,phone,' . $userId,
            'birth_date'  => 'nullable|date',
            'gender'      => 'nullable|in:male,female',
            'district_id' => 'nullable|exists:districts,id',
            'address'     => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'اسم المريض مطلوب',
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.unique'   => 'رقم الهاتف مستخدم بالفعل لمريض آخر',
        ];
    }
}
