<?php

namespace App\Http\Controllers\Api\V1\Admin\MedicalDictionary;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalDictionary\StoreTestGroupRequest;
use App\Http\Requests\MedicalDictionary\ToggleStatusRequest;
use App\Http\Requests\MedicalDictionary\UpdateTestGroupRequest;
use App\Http\Resources\MedicalDictionary\TestGroupResource;
use App\Models\TestGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TestGroupController extends Controller
{
    /**
     * جلب المجموعات المخبرية وتصفيتها
     */
    public function index(Request $request)
    {
        $query = TestGroup::withCount('tests');

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($query) use ($q) {
                $query->where('name_ar', 'like', "%{$q}%")
                      ->orWhere('name_en', 'like', "%{$q}%")
                      ->orWhere('key', 'like', "%{$q}%");
            });
        }

        $groups = $query->orderBy('sort_order', 'asc')->get();

        return response()->json([
            'status'      => true,
            'message'     => 'تم جلب المجموعات بنجاح',
            'groups'      => TestGroupResource::collection($groups),
            'totalGroups' => $groups->count(),
        ]);
    }

    /**
     * إضافة مجموعة مخبرية جديدة
     */
    public function store(StoreTestGroupRequest $request)
    {
        $group = TestGroup::create($request->validated());

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'status'  => true,
            'message' => 'تم إضافة المجموعة بنجاح',
            'group'   => new TestGroupResource($group->loadCount('tests')),
        ], 201);
    }

    /**
     * تحديث بيانات مجموعة مخبرية
     */
    public function update(UpdateTestGroupRequest $request, $id)
    {
        $group = TestGroup::findOrFail($id);
        $group->update($request->validated());

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث المجموعة بنجاح',
            'group'   => new TestGroupResource($group->loadCount('tests')),
        ]);
    }

    /**
     * تفعيل / إيقاف عرض المجموعة للمرضى
     */
    public function toggleStatus(ToggleStatusRequest $request, $id)
    {
        $group = TestGroup::findOrFail($id);
        $group->update(['is_active' => $request->boolean('is_active')]);

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'status'  => true,
            'message' => $group->is_active ? 'تم تفعيل المجموعة لمرضى المختبر' : 'تم إيقاف عرض المجموعة للمرضى',
            'group'   => new TestGroupResource($group->loadCount('tests')),
        ]);
    }

    /**
     * حذف المجموعة المخبرية
     */
    public function destroy($id)
    {
        $group = TestGroup::findOrFail($id);
        $group->delete();

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف المجموعة وتحاليلها بنجاح',
        ]);
    }
}
