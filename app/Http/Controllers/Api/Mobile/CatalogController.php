<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\MobileCatalogGroupResource;
use App\Http\Resources\Mobile\MobilePackageResource;
use App\Http\Resources\Mobile\MobileCatalogTestResource;
use App\Models\MedicalTest;
use App\Models\PackageOffer;
use App\Models\TestGroup;
use App\Services\Coverage\Contracts\CoverageEngineInterface;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(private CoverageEngineInterface $coverageEngine) {}
    /**
     * كتالوج التحاليل للموبايل — المجموعات + التحاليل + الباقات
     */
    public function catalog(Request $request)
    {
        $search = trim($request->get('search') ?: $request->get('q', ''));

        // المجموعات مع التحاليل المفعلة التي لها سعر
        $groupsQuery = TestGroup::where('is_active', true);

        if ($search !== '') {
            $groupsQuery->whereHas('tests', function ($q) use ($search) {
                $q->where('is_active', true)
                  ->where('total_price', '>', 0)
                  ->where(function ($sub) use ($search) {
                      $sub->where('name_ar', 'LIKE', "%{$search}%")
                          ->orWhere('name_en', 'LIKE', "%{$search}%");
                  });
            });
        } else {
            $groupsQuery->whereHas('tests', fn($q) => $q->where('is_active', true)->where('total_price', '>', 0));
        }

        $groups = $groupsQuery
            ->with(['tests' => function ($q) use ($search) {
                $q->where('is_active', true)
                  ->where('total_price', '>', 0);

                if ($search !== '') {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('name_ar', 'LIKE', "%{$search}%")
                            ->orWhere('name_en', 'LIKE', "%{$search}%");
                    });
                }

                $q->orderBy('sort_order')
                  ->select(['id', 'test_group_id', 'name_ar', 'name_en', 'total_price', 'fasting_required', 'result_time', 'description']);
            }])
            ->orderBy('sort_order')
            ->get(['id', 'name_ar', 'name_en', 'icon', 'color']);

        // الباقات المفعلة
        $packagesQuery = PackageOffer::where('is_active', true);
        if ($search !== '') {
            $packagesQuery->where(function ($q) use ($search) {
                $q->where('name_ar', 'LIKE', "%{$search}%")
                  ->orWhere('description_ar', 'LIKE', "%{$search}%");
            });
        }

        $packages = $packagesQuery
            ->with('tests:id,name_ar')
            ->get(['id', 'name_ar', 'description_ar', 'original_price', 'discount_price', 'image']);

        // ─── فحص التغطية الجغرافية (اختياري) ──────────────────────────────────
        // إذا أرسل التطبيق lat/lng، نضم معلومات التغطية مع الكتالوج
        // هذا يتيح للفرونت عرض شارة "مغطى / غير مغطى" فور فتح الصفحة
        $coverage = null;
        if ($request->filled('lat') && $request->filled('lng')) {
            $lat = (float) $request->lat;
            $lng = (float) $request->lng;

            if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                $result = $this->coverageEngine->verifyCoverage($lat, $lng);

                $coverage = [
                    'covered'     => $result->isCovered,
                    'zone_name'   => $result->zone?->name,
                    'service_fee' => $result->isCovered ? $result->fee : null,
                    'match_type'  => $result->matchType ?? null,
                    'message'     => $result->message,
                ];
            }
        }

        return response()->json([
            'status'   => true,
            'coverage' => $coverage,   // null إذا لم يُرسل الموقع
            'groups'   => MobileCatalogGroupResource::collection($groups),
            'packages' => MobilePackageResource::collection($packages),
        ]);
    }

    /**
     * تفاصيل تحليل واحد (الاسم، السعر، الوصف، الصيام، وقت النتيجة)
     */
    public function testDetails($id)
    {
        $test = MedicalTest::where('is_active', true)
            ->where('total_price', '>', 0)
            ->with('group:id,name_ar,icon,color')
            ->find($id);

        if (!$test) {
            return response()->json([
                'status'  => false,
                'message' => 'التحليل المطلوب غير متوفر أو غير مفعّل حالياً',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'test'   => new MobileCatalogTestResource($test),
        ]);
    }
}
