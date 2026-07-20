<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDistrictRequest;
use App\Http\Requests\UpdateDistrictRequest;
use App\Http\Resources\DistrictResource;
use App\Models\District;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    /**
     * إنشاء الأقضية والمناطق الأولية (بعقوبة، كنعان، الخالص) في حال كان الجدول فارغاً
     */
    protected function ensureDefaultDistrictsExist(): void
    {
        if (District::count() === 0) {
            // بعقوبة
            District::create([
                'name'        => 'بعقوبة',
                'governorate' => 'ديالى',
                'sort_order'  => 1,
                'is_active'   => true,
            ]);

            // كنعان
            District::create([
                'name'        => 'كنعان',
                'governorate' => 'ديالى',
                'sort_order'  => 2,
                'is_active'   => true,
            ]);

            // الخالص
            District::create([
                'name'        => 'الخالص',
                'governorate' => 'ديالى',
                'sort_order'  => 3,
                'is_active'   => true,
            ]);
        }
    }

    /**
     * قائمة الأقضية ومناطقها
     */
    public function index()
    {
        $this->ensureDefaultDistrictsExist();

        $districts = District::with(['branch'])
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $branches = \App\Models\Branch::select('id', 'name_ar', 'phone', 'is_active')->get();

        return response()->json([
            'status'         => true,
            'message'        => 'تم جلب قائمة الأقضية بنجاح',
            'districts'      => DistrictResource::collection($districts),
            'branches'       => $branches,
            'totalDistricts' => $districts->count(),
            'summary'        => [
                'activeDistricts'   => District::where('is_active', true)->count(),
                'inactiveDistricts' => District::where('is_active', false)->count(),
            ],
        ]);
    }

    /**
     * إضافة قضاء جديد
     */
    public function store(StoreDistrictRequest $request)
    {
        $data = $request->validated();
        if (empty($data['sort_order'])) {
            $max = District::max('sort_order');
            $data['sort_order'] = $max ? $max + 1 : 1;
        }
        if (empty($data['governorate'])) {
            $data['governorate'] = 'ديالى';
        }

        $district = District::create($data);

        return response()->json([
            'status'   => true,
            'message'  => 'تم إضافة القضاء بنجاح',
            'district' => new DistrictResource($district->load(['branch'])),
        ], 201);
    }

    /**
     * عرض قضاء
     */
    public function show(District $district)
    {
        return response()->json([
            'status'   => true,
            'district' => new DistrictResource($district->load(['branch'])),
        ]);
    }

    /**
     * تحديث قضاء
     */
    public function update(UpdateDistrictRequest $request, District $district)
    {
        $district->update($request->validated());

        return response()->json([
            'status'   => true,
            'message'  => 'تم تحديث القضاء بنجاح',
            'district' => new DistrictResource($district->load(['branch'])),
        ]);
    }

    /**
     * حذف قضاء
     */
    public function destroy(District $district)
    {
        $district->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف القضاء بنجاح',
        ]);
    }

    /**
     * تفعيل/إيقاف القضاء
     */
    public function toggleActive(District $district)
    {
        $district->update(['is_active' => !$district->is_active]);

        return response()->json([
            'status'   => true,
            'message'  => $district->is_active ? 'تم تفعيل القضاء بنجاح' : 'تم إخفاء القضاء بنجاح',
            'district' => new DistrictResource($district->load(['branch'])),
        ]);
    }
}
