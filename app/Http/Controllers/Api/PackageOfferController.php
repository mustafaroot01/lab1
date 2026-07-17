<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Packages\UploadPackageImageRequest;
use App\Http\Requests\StorePackageOfferRequest;
use App\Http\Requests\UpdatePackageOfferRequest;
use App\Http\Resources\PackageOfferResource;
use App\Models\PackageOffer;
use App\Models\MedicalTest;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PackageOfferController extends Controller
{
    /**
     * جلب قائمة الباقات والعروض (مع البحث والفلترة والترتيب والتصفح)
     */
    public function index(Request $request)
    {
        $query = PackageOffer::withCount('tests');

        // Search filter
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->where('name_ar', 'like', "%{$q}%")
                         ->orWhere('name_en', 'like', "%{$q}%")
                         ->orWhere('description_ar', 'like', "%{$q}%");
            });
        }

        // Status filter
        if ($request->filled('status') && $request->input('status') !== 'all') {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Sorting
        $sortBy = $request->input('sortBy', 'sort_order');
        if (is_array($sortBy) && isset($sortBy[0]['key'])) {
            $sortBy = $sortBy[0]['key'];
            $orderBy = $sortBy[0]['order'] ?? 'asc';
        } else {
            $orderBy = $request->input('orderBy', 'asc');
        }

        if (in_array($sortBy, ['id', 'name_ar', 'original_price', 'discount_price', 'sort_order', 'is_active', 'tests_count'])) {
            $query->orderBy($sortBy, $orderBy === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('sort_order', 'asc');
        }

        $totalPackagesCount = $query->count();

        // Pagination
        $itemsPerPage = (int) $request->input('itemsPerPage', 10);
        if ($itemsPerPage === -1) {
            $itemsPerPage = $totalPackagesCount > 0 ? $totalPackagesCount : 10;
        }

        $packageOffers = $query->paginate($itemsPerPage);

        // إحصائيات باستعلام واحد بدلاً من جلب كل السجلات في الذاكرة
        $stats = PackageOffer::selectRaw("
            COUNT(*) as total,
            SUM(is_active) as active,
            SUM(1 - is_active) as inactive
        ")->first();

        $globalSectionActive = SystemSetting::getBoolean('package_offers_active', true);

        return response()->json([
            'status'                => true,
            'message'               => 'تم جلب قائمة الباقات والعروض بنجاح',
            'package_offers'        => PackageOfferResource::collection($packageOffers),
            'totalPackageOffers'    => $totalPackagesCount,
            'global_section_active' => $globalSectionActive,
            'summary'               => [
                'totalPackages'    => (int) $stats->total,
                'activePackages'   => (int) $stats->active,
                'inactivePackages' => (int) $stats->inactive,
            ],
        ]);

    }

    /**
     * إضافة باقة أو عرض مخبري جديد مع التحاليل المشمولة
     */
    public function store(StorePackageOfferRequest $request)
    {
        $validated = $request->validated();
        $tests = $validated['tests'] ?? [];
        unset($validated['tests']);

        $packageOffer = PackageOffer::create($validated);

        if (!empty($tests)) {
            $packageOffer->tests()->sync($tests);
        }

        return response()->json([
            'status' => true,
            'message' => 'تم إضافة الباقة أو العرض بنجاح',
            'package_offer' => new PackageOfferResource($packageOffer->load('tests')),
        ], 201);
    }

    /**
     * عرض تفاصيل باقة مخبرية محددة مع التحاليل التابعة لها
     */
    public function show($id)
    {
        $packageOffer = PackageOffer::with('tests')->findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => 'تم جلب تفاصيل العرض المخبري بنجاح',
            'package_offer' => new PackageOfferResource($packageOffer),
        ]);
    }

    /**
     * تحديث بيانات الباقة أو العرض المخبري والتحاليل المرتبطة به
     */
    public function update(UpdatePackageOfferRequest $request, $id)
    {
        $packageOffer = PackageOffer::findOrFail($id);
        $validated = $request->validated();

        if (isset($validated['tests'])) {
            $tests = $validated['tests'];
            unset($validated['tests']);
            $packageOffer->tests()->sync($tests);
        }

        $packageOffer->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث بيانات الباقة المخبرية بنجاح',
            'package_offer' => new PackageOfferResource($packageOffer->load('tests')),
        ]);
    }

    /**
     * حذف باقة أو عرض مخبري من النظام
     */
    public function destroy($id)
    {
        $packageOffer = PackageOffer::findOrFail($id);
        $packageOffer->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف العرض المخبري بنجاح',
        ]);
    }

    /**
     * تفعيل أو تعطيل ظهور الباقة المخبرية في تطبيق المراجعين
     */
    public function toggleStatus(Request $request, $id)
    {
        $packageOffer = PackageOffer::findOrFail($id);
        $packageOffer->update(['is_active' => $request->boolean('is_active')]);

        return response()->json([
            'status' => true,
            'message' => $packageOffer->is_active ? 'تم تفعيل العرض المخبري وبداية ظهوره في التطبيق' : 'تم إخفاء العرض مؤقتاً من التطبيق',
            'package_offer' => new PackageOfferResource($packageOffer->load('tests')),
        ]);
    }

    /**
     * تفعيل أو إيقاف قسم العروض والباقات بالكامل في التطبيق (Global Switch)
     */
    public function toggleGlobalSection(Request $request)
    {
        $isActive = $request->boolean('is_active');
        SystemSetting::setValue('package_offers_active', $isActive);

        return response()->json([
            'status' => true,
            'message' => $isActive ? 'تم تفعيل وإظهار صفحة العروض والباقات بالكامل في التطبيق' : 'تم إيقاف وإخفاء صفحة العروض بالكامل من التطبيق',
            'global_section_active' => $isActive,
        ]);
    }

    /**
     * جلب قائمة كافة التحاليل المخبرية النشطة لاختيارها عند إنشاء باقة جديدة
     */
    public function availableTests(Request $request)
    {
        $query = MedicalTest::query()
            ->where('is_active', true)
            ->where('total_price', '>', 0);

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->where('name_ar', 'like', "%{$q}%")
                         ->orWhere('name_en', 'like', "%{$q}%")
                         ->orWhere('key', 'like', "%{$q}%");
            });
        }

        $tests = $query->orderBy('name_ar')->select([
            'id', 'name_ar', 'name_en', 'key', 'price', 'platform_price', 'total_price'
        ])->get();

        return response()->json([
            'status' => true,
            'message' => 'تم جلب قائمة التحاليل المتاحة بنجاح',
            'tests' => $tests,
        ]);
    }

    /**
     * رفع صورة الباقة أو العرض المخبري وإرجاع رابط الصورة
     */
    public function uploadImage(UploadPackageImageRequest $request)
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('packages', $file, $filename);
            $url = asset('storage/packages/' . $filename);

            return response()->json([
                'status' => true,
                'message' => 'تم رفع الصورة بنجاح',
                'url' => $url,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'لم يتم إرسال أي ملف صورة',
        ], 400);
    }
}
