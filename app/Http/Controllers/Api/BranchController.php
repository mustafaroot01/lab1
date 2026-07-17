<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branches\CheckCoverageRequest;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * قائمة الفروع مع بحث وتصفية وتصفح
     */
    public function index(Request $request)
    {
        $query = Branch::with('districts');

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($qb) use ($q) {
                $qb->where('name_ar', 'like', "%{$q}%")
                   ->orWhere('address', 'like', "%{$q}%")
                   ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $sortBy = $request->input('sortBy', 'id');
        $orderBy = $request->input('orderBy', 'asc');

        if (in_array($sortBy, ['id', 'name_ar', 'radius_km', 'is_active', 'created_at'])) {
            $query->orderBy($sortBy, $orderBy === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('id', 'asc');
        }

        $totalCount = $query->count();

        $itemsPerPage = (int) $request->input('itemsPerPage', 10);
        if ($itemsPerPage === -1) {
            $itemsPerPage = max($totalCount, 1);
        }

        $branches = $query->paginate($itemsPerPage);

        $all = Branch::all();
        $summary = [
            'totalBranches'    => $all->count(),
            'activeBranches'   => $all->where('is_active', true)->count(),
            'inactiveBranches' => $all->where('is_active', false)->count(),
        ];

        return response()->json([
            'status'       => true,
            'message'      => 'تم جلب قائمة الفروع بنجاح',
            'branches'     => BranchResource::collection($branches->items()),
            'allDistricts' => \App\Models\District::select('id', 'name', 'governorate')->orderBy('sort_order')->get(),
            'totalBranches'=> $totalCount,
            'summary'      => $summary,
        ]);
    }

    /**
     * إضافة فرع جديد
     */
    public function store(StoreBranchRequest $request)
    {
        $branch = Branch::create($request->validated());

        if ($request->has('district_ids')) {
            if (is_array($request->input('district_ids')) && count($request->input('district_ids')) > 0) {
                \App\Models\District::whereIn('id', $request->input('district_ids'))->update(['branch_id' => $branch->id]);
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم إضافة الفرع بنجاح',
            'branch'  => new BranchResource($branch->load('districts')),
        ], 201);
    }

    /**
     * تفاصيل فرع
     */
    public function show(Branch $branch)
    {
        return response()->json([
            'status'  => true,
            'message' => 'تم جلب تفاصيل الفرع بنجاح',
            'branch'  => new BranchResource($branch->load('districts')),
        ]);
    }

    /**
     * تعديل فرع
     */
    public function update(UpdateBranchRequest $request, Branch $branch)
    {
        $branch->update($request->validated());

        if ($request->has('district_ids')) {
            \App\Models\District::where('branch_id', $branch->id)->update(['branch_id' => null]);
            if (is_array($request->input('district_ids')) && count($request->input('district_ids')) > 0) {
                \App\Models\District::whereIn('id', $request->input('district_ids'))->update(['branch_id' => $branch->id]);
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث بيانات الفرع بنجاح',
            'branch'  => new BranchResource($branch->fresh('districts')),
        ]);
    }

    /**
     * حذف فرع
     */
    public function destroy(Branch $branch)
    {
        $branch->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف الفرع بنجاح',
        ]);
    }

    /**
     * تفعيل / إيقاف الفرع
     */
    public function toggleActive(Branch $branch)
    {
        $branch->update(['is_active' => !$branch->is_active]);

        return response()->json([
            'status'    => true,
            'message'   => $branch->is_active ? 'تم تفعيل الفرع بنجاح' : 'تم إيقاف الفرع بنجاح',
            'is_active' => $branch->is_active,
        ]);
    }

    /**
     * التحقق من تغطية موقع معين (للتطبيق)
     * POST /api/check-coverage
     * Body: { lat: float, lng: float }
     */
    public function checkCoverage(CheckCoverageRequest $request)
    {
        // 1. الفحص المباشر عبر القضاء (District) — وهو الأساس المعتمد عندما يكون المختبر مرتبطاً مباشرة بالقضاء
        if ($request->filled('district_id')) {
            $district = \App\Models\District::with('branch')->where('id', $request->district_id)->where('is_active', true)->first();

            if ($district) {
                // الفرع المرتبط بالقضاء أو المختبر الوحيد النشط في النظام
                $branch = $district->branch ?? Branch::where('is_active', true)->first();

                return response()->json([
                    'status'   => true,
                    'covered'  => true,
                    'message'  => 'الخدمة متوفرة في القضاء المختار (' . $district->name . ')',
                    'district' => [
                        'id'             => $district->id,
                        'name'           => $district->name,
                        'service_fee'    => $district->service_fee ?? ($branch?->service_fee ?? 0),
                        'free_threshold' => $district->free_threshold ?? ($branch?->free_threshold ?? 0),
                    ],
                    'branch'   => $branch ? new BranchResource($branch) : null,
                ]);
            }

            return response()->json([
                'status'  => true,
                'covered' => false,
                'message' => 'عذراً، الخدمة غير متوفرة حالياً في هذا القضاء أو تم إيقافها مؤقتاً.',
            ]);
        }

        // 2. الفحص عبر الإحداثيات (في حال تم تمرير lat و lng بدلاً من القضاء)
        if ($request->filled('lat') && $request->filled('lng')) {
            $lat = (float) $request->input('lat');
            $lng = (float) $request->input('lng');

            $activeBranches = Branch::where('is_active', true)
                ->whereNotNull('lat')
                ->whereNotNull('lng')
                ->get();

            $coveredBranch = null;
            $minDistance = PHP_FLOAT_MAX;

            foreach ($activeBranches as $branch) {
                $distance = $branch->haversineDistance($lat, $lng);
                if ($branch->coversLocation($lat, $lng) && $distance < $minDistance) {
                    $minDistance = $distance;
                    $coveredBranch = $branch;
                    $coveredBranch->distance_km = $distance;
                }
            }

            // إذا لم يتم تحديد إحداثيات للفرع وكان يوجد مختبر واحد نشط فقط في النظام، نرجع التغطية بنجاح
            if (!$coveredBranch && Branch::where('is_active', true)->count() === 1) {
                $singleBranch = Branch::where('is_active', true)->first();
                return response()->json([
                    'status'  => true,
                    'covered' => true,
                    'message' => 'الخدمة متوفرة في منطقتك من خلال المختبر الرئيسي',
                    'branch'  => new BranchResource($singleBranch),
                ]);
            }

            if ($coveredBranch) {
                return response()->json([
                    'status'  => true,
                    'covered' => true,
                    'message' => 'الخدمة متوفرة في منطقتك',
                    'branch'  => new BranchResource($coveredBranch),
                ]);
            }
        }

        return response()->json([
            'status'  => true,
            'covered' => false,
            'message' => 'عذراً، يرجى اختيار القضاء للتحقق من توفر الخدمة أو الخدمة غير متوفرة في موقعك حالياً.',
        ]);
    }
}
