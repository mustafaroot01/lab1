<?php

namespace App\Queries;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PatientSearch
{
    /**
     * بناء وتصفية استعلام المرضى للوحة الإدارة (مع دعم كافة الفلاتر)
     */
    public static function run(Request $request): Builder
    {
        $search     = trim($request->get('search', ''));
        $districtId = $request->get('district_id');
        $status     = $request->get('status');
        $gender     = $request->get('gender');

        return Patient::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%")
                        ->orWhere('id', $search);
                });
            })
            ->when($districtId, fn($q) => $q->where('district_id', $districtId))
            ->when($status, function ($q) use ($status) {
                if ($status === 'completed') {
                    $q->where('is_profile_completed', true);
                } elseif ($status === 'pending') {
                    $q->where('is_profile_completed', false);
                } elseif ($status === 'active' || $status === '1') {
                    $q->where('is_active', true);
                } elseif ($status === 'inactive' || $status === '0') {
                    $q->where('is_active', false);
                }
            })
            ->when($gender, fn($q) => $q->where('gender', $gender));
    }
}
