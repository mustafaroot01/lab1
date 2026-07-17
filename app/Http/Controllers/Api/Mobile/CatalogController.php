<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\MobileCatalogGroupResource;
use App\Http\Resources\Mobile\MobilePackageResource;
use App\Http\Resources\Mobile\MobileCatalogTestResource;
use App\Models\MedicalTest;
use App\Models\PackageOffer;
use App\Models\TestGroup;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
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

        return response()->json([
            'status'   => true,
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
