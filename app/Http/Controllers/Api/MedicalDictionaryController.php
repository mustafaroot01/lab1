<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalDictionary\StoreTestGroupRequest;
use App\Http\Requests\MedicalDictionary\UpdateTestGroupRequest;
use App\Http\Requests\MedicalDictionary\StoreMedicalTestRequest;
use App\Http\Requests\MedicalDictionary\UpdateMedicalTestRequest;
use App\Http\Requests\MedicalDictionary\StoreSampleTypeRequest;
use App\Http\Requests\MedicalDictionary\UpdateSampleTypeRequest;
use App\Http\Requests\MedicalDictionary\StoreTubeTypeRequest;
use App\Http\Requests\MedicalDictionary\UpdateTubeTypeRequest;
use App\Models\TestGroup;
use App\Models\MedicalTest;
use App\Models\SampleType;
use App\Models\TubeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MedicalDictionaryController extends Controller
{
    // ==========================================
    // Groups CRUD
    // ==========================================

    public function getGroups(Request $request)
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
            'groups' => $groups,
            'totalGroups' => $groups->count(),
        ]);
    }

    public function storeGroup(StoreTestGroupRequest $request)
    {
        $group = TestGroup::create($request->validated());

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'message' => 'تم إضافة المجموعة بنجاح',
            'group' => $group->loadCount('tests')
        ], 201);
    }

    public function updateGroup(UpdateTestGroupRequest $request, $id)
    {
        $group = TestGroup::findOrFail($id);
        $group->update($request->validated());

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'message' => 'تم تحديث المجموعة بنجاح',
            'group' => $group->loadCount('tests')
        ]);
    }

    public function toggleGroupStatus(Request $request, $id)
    {
        $group = TestGroup::findOrFail($id);
        $group->update(['is_active' => $request->boolean('is_active')]);

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'message' => $group->is_active ? 'تم تفعيل المجموعة لمرضى المختبر' : 'تم إيقاف عرض المجموعة للمرضى',
            'group' => $group->loadCount('tests')
        ]);
    }

    public function destroyGroup($id)
    {
        $group = TestGroup::findOrFail($id);
        $group->delete();

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'message' => 'تم حذف المجموعة وتحاليلها بنجاح'
        ]);
    }

    // ==========================================
    // Medical Tests CRUD
    // ==========================================

    public function getTests(Request $request)
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
        if ($itemsPerPage === -1) {
            $itemsPerPage = $totalTests > 0 ? $totalTests : 10;
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
            'tests' => $tests,
            'totalTests' => $totalTests,
            'page' => $page,
            'itemsPerPage' => $itemsPerPage,
        ]);
    }

    public function storeTest(StoreMedicalTestRequest $request)
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
            'message' => 'تم إضافة التحليل بنجاح',
            'test' => $test->load(['group', 'sampleTypeObj', 'tubeTypeObj'])
        ], 201);
    }

    public function updateTest(UpdateMedicalTestRequest $request, $id)
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
            'message' => 'تم تحديث التحليل بنجاح',
            'test' => $test->load(['group', 'sampleTypeObj', 'tubeTypeObj'])
        ]);
    }

    public function toggleTestStatus(Request $request, $id)
    {
        $test = MedicalTest::findOrFail($id);
        $test->update(['is_active' => $request->boolean('is_active')]);

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'message' => $test->is_active ? 'تم تفعيل وإظهار التحليل في تطبيق المرضى' : 'تم إيقاف عرض التحليل للمرضى',
            'test' => $test->load(['group', 'sampleTypeObj', 'tubeTypeObj'])
        ]);
    }

    public function destroyTest($id)
    {
        $test = MedicalTest::findOrFail($id);
        $test->delete();

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'message' => 'تم حذف التحليل بنجاح'
        ]);
    }

    // ==========================================
    // Patient App Catalog API (Only Active Groups & Tests with Price > 0)
    // ==========================================
    public function getPatientCatalog()
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
                'catalog' => $groups,
                'totalActiveGroups' => $groups->count(),
                'totalActiveTests' => $groups->sum(fn($g) => $g->tests->count()),
            ]);
        });
    }

    public function getSummary()
    {
        return Cache::remember('medical_summary_json', 3600, function () {
            return response()->json([
                'totalTests' => MedicalTest::count(),
                'totalGroups' => TestGroup::count(),
                'fastingTests' => MedicalTest::where('fasting_required', true)->count(),
                'totalSampleTypes' => SampleType::count(),
                'totalTubeTypes' => TubeType::count(),
                'sampleTypes' => SampleType::orderBy('sort_order', 'asc')->get(),
                'tubeTypes' => TubeType::orderBy('sort_order', 'asc')->get(),
            ]);
        });
    }

    // ==========================================
    // Sample Types CRUD
    // ==========================================

    public function getSampleTypes(Request $request)
    {
        $query = SampleType::query();

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($query) use ($q) {
                $query->where('name_ar', 'like', "%{$q}%")
                      ->orWhere('name_en', 'like', "%{$q}%")
                      ->orWhere('code', 'like', "%{$q}%");
            });
        }

        $samples = $query->orderBy('sort_order', 'asc')->get();

        // Attach tests_count attribute to JSON
        $samples->each->append('tests_count');

        return response()->json([
            'sampleTypes' => $samples,
            'totalSampleTypes' => $samples->count(),
        ]);
    }

    public function storeSampleType(StoreSampleTypeRequest $request)
    {
        $sample = SampleType::create($request->validated());
        $sample->append('tests_count');

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'message' => 'تم إضافة نوع العينة بنجاح',
            'sampleType' => $sample
        ], 201);
    }

    public function updateSampleType(UpdateSampleTypeRequest $request, $id)
    {
        $sample = SampleType::findOrFail($id);
        $sample->update($request->validated());
        if ($sample->wasChanged('name_ar')) {
            $sample->tests()->update(['sample_type' => $sample->name_ar]);
        }
        $sample->append('tests_count');

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'message' => 'تم تحديث نوع العينة بنجاح',
            'sampleType' => $sample
        ]);
    }

    public function destroySampleType($id)
    {
        $sample = SampleType::findOrFail($id);
        $sample->delete();

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'message' => 'تم حذف نوع العينة بنجاح'
        ]);
    }

    // ==========================================
    // Tube Types CRUD
    // ==========================================

    public function getTubeTypes(Request $request)
    {
        $query = TubeType::query();

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($query) use ($q) {
                $query->where('name_ar', 'like', "%{$q}%")
                      ->orWhere('name_en', 'like', "%{$q}%")
                      ->orWhere('cap_color', 'like', "%{$q}%")
                      ->orWhere('code', 'like', "%{$q}%");
            });
        }

        $totalTubes = $query->count();

        // Sorting
        $sortBy = $request->input('sortBy', 'id');
        $orderBy = $request->input('orderBy', 'asc');
        if (in_array($sortBy, ['id', 'name_ar', 'name_en', 'cap_color', 'sort_order'])) {
            $query->orderBy($sortBy, $orderBy === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
        }

        $tubes = $query->get()->each->append('tests_count');

        return response()->json([
            'tubeTypes' => $tubes,
            'totalTubeTypes' => $totalTubes,
        ]);
    }

    public function storeTubeType(StoreTubeTypeRequest $request)
    {
        $tube = TubeType::create($request->validated());
        $tube->append('tests_count');

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'message' => 'تم إضافة أنبوب سحب بنجاح',
            'tubeType' => $tube
        ], 201);
    }

    public function updateTubeType(UpdateTubeTypeRequest $request, $id)
    {
        $tube = TubeType::findOrFail($id);
        $tube->update($request->validated());
        if ($tube->wasChanged('name_ar')) {
            $tube->tests()->update(['tube_type' => $tube->name_ar]);
        }
        $tube->append('tests_count');

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'message' => 'تم تحديث أنبوب السحب بنجاح',
            'tubeType' => $tube
        ]);
    }

    public function destroyTubeType($id)
    {
        $tube = TubeType::findOrFail($id);
        $tube->delete();

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'message' => 'تم حذف أنبوب السحب بنجاح'
        ]);
    }
}
