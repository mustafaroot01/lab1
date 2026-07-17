<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\MobilePackageResource;
use App\Models\PackageOffer;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * قائمة كافة الباقات الطبية والشاملة المتاحة في تطبيق الموبايل (لصفحة الباقات المستقلة)
     */
    public function packages(Request $request)
    {
        $search = trim($request->get('search') ?: $request->get('q', ''));

        $query = PackageOffer::where('is_active', true)
            ->with(['tests' => fn($q) => $q->select([
                'medical_tests.id',
                'medical_tests.name_ar',
                'medical_tests.name_en',
                'medical_tests.fasting_required',
                'medical_tests.result_time',
                'medical_tests.total_price',
            ])])
            ->orderBy('id', 'desc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'LIKE', "%{$search}%")
                  ->orWhere('description_ar', 'LIKE', "%{$search}%");
            });
        }

        $packages = $query->get();

        return response()->json([
            'status'   => true,
            'message'  => 'قائمة الباقات الطبية',
            'packages' => MobilePackageResource::collection($packages),
        ]);
    }

    /**
     * تفاصيل باقة معينة مع التحاليل المشمولة بها بالكامل
     */
    public function packageDetails($id)
    {
        $package = PackageOffer::where('is_active', true)
            ->with(['tests' => fn($q) => $q->select([
                'medical_tests.id',
                'medical_tests.name_ar',
                'medical_tests.name_en',
                'medical_tests.fasting_required',
                'medical_tests.result_time',
                'medical_tests.total_price',
            ])])
            ->find($id);

        if (!$package) {
            return response()->json([
                'status'  => false,
                'message' => 'الباقة المطلوبة غير متوفرة حالياً',
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'package' => new MobilePackageResource($package),
        ]);
    }
}
