<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTechnicianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $technician = $this->route('technician');
        $id = is_object($technician) ? $technician->id : $technician;

        return [
            'name'               => 'sometimes|required|string|max:255',
            'phone'              => "sometimes|required|string|max:20|unique:technicians,phone,{$id}",

            'password'           => 'nullable|string|min:6',
            'address'            => 'nullable|string|max:500',
            'specialty'          => 'nullable|string|max:255',
            'has_transport'      => 'boolean',
            'has_equipment'      => 'boolean',
            'id_front_image'     => 'nullable|string',
            'id_back_image'      => 'nullable|string',
            'district_id_image'  => 'nullable|string',
            'notes'              => 'nullable|string',
            'status'             => 'in:active,suspended,on_leave',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'اسم الفني مطلوب',
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.unique'   => 'رقم الهاتف مسجل مسبقاً',
            'password.min'   => 'كلمة السر يجب أن تكون 6 أحرف على الأقل',
        ];
    }
}
