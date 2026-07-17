<?php

namespace App\Http\Controllers\Api\V1\Admin\MedicalDictionary;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalDictionary\StoreTubeTypeRequest;
use App\Http\Requests\MedicalDictionary\UpdateTubeTypeRequest;
use App\Http\Resources\MedicalDictionary\TubeTypeResource;
use App\Models\TubeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TubeTypeController extends Controller
{
    /**
     * قائمة أنواع أنابيب السحب
     */
    public function index(Request $request)
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
            'status'         => true,
            'message'        => 'تم جلب أنواع الأنابيب بنجاح',
            'tubeTypes'      => TubeTypeResource::collection($tubes),
            'totalTubeTypes' => $totalTubes,
        ]);
    }

    /**
     * إضافة أنبوب سحب جديد
     */
    public function store(StoreTubeTypeRequest $request)
    {
        $tube = TubeType::create($request->validated());
        $tube->append('tests_count');

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'status'   => true,
            'message'  => 'تم إضافة أنبوب سحب بنجاح',
            'tubeType' => new TubeTypeResource($tube),
        ], 201);
    }

    /**
     * تحديث بيانات أنبوب سحب وتعديل تحاليله
     */
    public function update(UpdateTubeTypeRequest $request, $id)
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
            'status'   => true,
            'message'  => 'تم تحديث أنبوب السحب بنجاح',
            'tubeType' => new TubeTypeResource($tube),
        ]);
    }

    /**
     * حذف أنبوب سحب
     */
    public function destroy($id)
    {
        $tube = TubeType::findOrFail($id);
        $tube->delete();

        Cache::forget('patient_catalog_json');
        Cache::forget('medical_summary_json');

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف أنبوب السحب بنجاح',
        ]);
    }
}
