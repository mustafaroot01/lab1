<?php

namespace App\Http\Controllers\Api\V1\Admin\MedicalDictionary;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalDictionary\StoreSampleTypeRequest;
use App\Http\Requests\MedicalDictionary\UpdateSampleTypeRequest;
use App\Http\Resources\MedicalDictionary\SampleTypeResource;
use App\Models\SampleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SampleTypeController extends Controller
{
    /**
     * قائمة أنواع العينات المخبرية
     */
    public function index(Request $request)
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
        $samples->each->append('tests_count');

        return response()->json([
            'status'           => true,
            'message'          => 'تم جلب أنواع العينات بنجاح',
            'sampleTypes'      => SampleTypeResource::collection($samples),
            'totalSampleTypes' => $samples->count(),
        ]);
    }

    /**
     * إضافة نوع عينة جديد
     */
    public function store(StoreSampleTypeRequest $request)
    {
        $sample = SampleType::create($request->validated());
        $sample->append('tests_count');

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'status'     => true,
            'message'    => 'تم إضافة نوع العينة بنجاح',
            'sampleType' => new SampleTypeResource($sample),
        ], 201);
    }

    /**
     * تحديث نوع العينة وتعديل ارتباطات التحاليل التابعة
     */
    public function update(UpdateSampleTypeRequest $request, $id)
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
            'status'     => true,
            'message'    => 'تم تحديث نوع العينة بنجاح',
            'sampleType' => new SampleTypeResource($sample),
        ]);
    }

    /**
     * حذف نوع العينة
     */
    public function destroy($id)
    {
        $sample = SampleType::findOrFail($id);
        $sample->delete();

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف نوع العينة بنجاح',
        ]);
    }
}
