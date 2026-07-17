<?php

namespace App\Http\Controllers\Api\V1\Admin\MedicalDictionary;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalDictionary\StoreMedicalTestRequest;
use App\Http\Requests\MedicalDictionary\ToggleStatusRequest;
use App\Http\Requests\MedicalDictionary\UpdateMedicalTestRequest;
use App\Http\Resources\MedicalDictionary\MedicalTestResource;
use App\Http\Resources\MedicalDictionary\SampleTypeResource;
use App\Http\Resources\MedicalDictionary\TubeTypeResource;
use App\Models\MedicalTest;
use App\Models\SampleType;
use App\Models\TestGroup;
use App\Models\TubeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MedicalTestController extends Controller
{
    /**
     * جلب قائمة التحاليل المخبرية مع التصفية والفرز
     */
    public function index(Request $request)
    {
        $query = MedicalTest::with(['group', 'sampleTypeObj', 'tubeTypeObj']);

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($query) use ($q) {
                $query->where('name_ar', 'like', "%{$q}%")
                      ->orWhere('name_en', 'like', "%{$q}%")
                      ->orWhere('key', 'like', "%{$q}%");
            });
        }

        if ($request->filled('group_id') && $request->input('group_id') !== 'all') {
            $query->where('test_group_id', $request->input('group_id'));
        }

        if ($request->filled('sample_type') && $request->input('sample_type') !== 'all') {
            $query->where(function($q) use ($request) {
                $q->where('sample_type', $request->input('sample_type'))
                  ->orWhereHas('sampleTypeObj', function($sq) use ($request) {
                      $sq->where('name_ar', $request->input('sample_type'));
                  });
            });
        }

        if ($request->filled('fasting_required') && $request->input('fasting_required') !== 'all') {
            $fasting = filter_var($request->input('fasting_required'), FILTER_VALIDATE_BOOLEAN);
            $query->where('fasting_required', $fasting);
        }

        $totalTests = $query->count();

        // Pagination
        $itemsPerPage = (int) $request->input('itemsPerPage', 10);
        if ($itemsPerPage === -1 || $itemsPerPage > 1000) {
            $itemsPerPage = max($totalTests, 1);
        }
        $page = (int) $request->input('page', 1);

        // Sorting
        $sortBy = $request->input('sortBy', 'id');
        $orderBy = $request->input('orderBy', 'desc');
        if (in_array($sortBy, ['id', 'name_ar', 'name_en', 'sample_type', 'sort_order'])) {
            $query->orderBy($sortBy, $orderBy === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
        }

        $tests = $query->skip(($page - 1) * $itemsPerPage)->take($itemsPerPage)->get();

        return response()->json([
            'status'       => true,
            'message'      => 'تم جلب التحاليل بنجاح',
            'tests'        => MedicalTestResource::collection($tests),
            'totalTests'   => $totalTests,
            'page'         => $page,
            'itemsPerPage' => $itemsPerPage,
        ]);
    }

    /**
     * إضافة تحليل طبي مخبري جديد
     */
    public function store(StoreMedicalTestRequest $request)
    {
        $validated = $request->validated();

        if (empty($validated['sample_type_id']) && !empty($validated['sample_type'])) {
            $validated['sample_type_id'] = SampleType::where('name_ar', $validated['sample_type'])->first()?->id;
        } elseif (!empty($validated['sample_type_id']) && empty($validated['sample_type'])) {
            $validated['sample_type'] = SampleType::find($validated['sample_type_id'])?->name_ar;
        }

        if (empty($validated['tube_type_id']) && !empty($validated['tube_type'])) {
            $validated['tube_type_id'] = TubeType::where('name_ar', $validated['tube_type'])->first()?->id;
        } elseif (!empty($validated['tube_type_id']) && empty($validated['tube_type'])) {
            $validated['tube_type'] = TubeType::find($validated['tube_type_id'])?->name_ar;
        }

        if (isset($validated['price']) || isset($validated['platform_price'])) {
            $validated['total_price'] = (float)($validated['price'] ?? 0) + (float)($validated['platform_price'] ?? 0);
        }

        $test = MedicalTest::create($validated);

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'status'  => true,
            'message' => 'تم إضافة التحليل بنجاح',
            'test'    => new MedicalTestResource($test->load(['group', 'sampleTypeObj', 'tubeTypeObj'])),
        ], 201);
    }

    /**
     * تحديث بيانات التحليل الطبي المخبري
     */
    public function update(UpdateMedicalTestRequest $request, $id)
    {
        $test = MedicalTest::findOrFail($id);
        $validated = $request->validated();

        if (empty($validated['sample_type_id']) && !empty($validated['sample_type'])) {
            $validated['sample_type_id'] = SampleType::where('name_ar', $validated['sample_type'])->first()?->id;
        } elseif (!empty($validated['sample_type_id']) && empty($validated['sample_type'])) {
            $validated['sample_type'] = SampleType::find($validated['sample_type_id'])?->name_ar;
        }

        if (empty($validated['tube_type_id']) && !empty($validated['tube_type'])) {
            $validated['tube_type_id'] = TubeType::where('name_ar', $validated['tube_type'])->first()?->id;
        } elseif (!empty($validated['tube_type_id']) && empty($validated['tube_type'])) {
            $validated['tube_type'] = TubeType::find($validated['tube_type_id'])?->name_ar;
        }

        if (isset($validated['price']) || isset($validated['platform_price'])) {
            $validated['total_price'] = (float)($validated['price'] ?? 0) + (float)($validated['platform_price'] ?? 0);
        }

        $test->update($validated);

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث التحليل بنجاح',
            'test'    => new MedicalTestResource($test->load(['group', 'sampleTypeObj', 'tubeTypeObj'])),
        ]);
    }

    /**
     * تفعيل أو تعطيل إظهار التحليل في الموبايل
     */
    public function toggleStatus(ToggleStatusRequest $request, $id)
    {
        $test = MedicalTest::findOrFail($id);
        $test->update(['is_active' => $request->boolean('is_active')]);

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'status'  => true,
            'message' => $test->is_active ? 'تم تفعيل وإظهار التحليل في تطبيق المرضى' : 'تم إيقاف عرض التحليل للمرضى',
            'test'    => new MedicalTestResource($test->load(['group', 'sampleTypeObj', 'tubeTypeObj'])),
        ]);
    }

    /**
     * حذف التحليل المخبري
     */
    public function destroy($id)
    {
        $test = MedicalTest::findOrFail($id);
        $test->delete();

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف التحليل بنجاح',
        ]);
    }

    /**
     * ملخص إحصاءات القاموس المخبري (Summary)
     */
    public function summary()
    {
        return Cache::remember('medical_summary_json', 3600, function () {
            $sampleTypes = SampleType::orderBy('sort_order', 'asc')->get();
            $tubeTypes   = TubeType::orderBy('sort_order', 'asc')->get();

            return response()->json([
                'status'           => true,
                'totalTests'       => MedicalTest::count(),
                'totalGroups'      => TestGroup::count(),
                'fastingTests'     => MedicalTest::where('fasting_required', true)->count(),
                'totalSampleTypes' => $sampleTypes->count(),
                'totalTubeTypes'   => $tubeTypes->count(),
                'sampleTypes'      => SampleTypeResource::collection($sampleTypes),
                'tubeTypes'        => TubeTypeResource::collection($tubeTypes),
            ]);
        });
    }

    /**
     * كتالوج تطبيق المراجعين العام (Active groups and tests with price > 0)
     */
    public function patientCatalog()
    {
        return Cache::remember('patient_catalog_json', 3600, function () {
            $groups = TestGroup::where('is_active', true)
                ->whereHas('tests', function ($q) {
                    $q->where('is_active', true)->where(function ($sub) {
                        $sub->where('price', '>', 0)->orWhere('total_price', '>', 0);
                    });
                })
                ->with(['tests' => function ($q) {
                    $q->where('is_active', true)
                      ->where(function ($sub) {
                          $sub->where('price', '>', 0)->orWhere('total_price', '>', 0);
                      })
                      ->with(['sampleTypeObj', 'tubeTypeObj'])
                      ->orderBy('sort_order', 'asc');
                }])
                ->orderBy('sort_order', 'asc')
                ->get();

            return response()->json([
                'status'            => true,
                'catalog'           => \App\Http\Resources\MedicalDictionary\TestGroupResource::collection($groups),
                'totalActiveGroups' => $groups->count(),
                'totalActiveTests'  => $groups->sum(fn($g) => $g->tests->count()),
            ]);
        });
    }
}
