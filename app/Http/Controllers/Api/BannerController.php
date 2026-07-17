<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * قائمة البنرات مع الإحصائيات
     */
    public function index(Request $request)
    {
        $query = Banner::query();

        // تصفية حسب المكان/القسم
        if ($request->filled('position') && $request->position !== 'all') {
            $query->where('position', $request->position);
        }

        // تصفية حسب الحالة
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        // ترتيب الظهور
        $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc');

        $banners = $query->get();

        return response()->json([
            'status'       => true,
            'message'      => 'تم جلب قائمة البنرات بنجاح',
            'banners'      => BannerResource::collection($banners),
            'totalBanners' => $banners->count(),
            'summary'      => [
                'total'     => Banner::count(),
                'active'    => Banner::where('is_active', true)->count(),
                'inactive'  => Banner::where('is_active', false)->count(),
            ],
        ]);
    }

    /**
     * إضافة بنر جديد
     */
    public function store(StoreBannerRequest $request)
    {
        $data = $request->validated();

        // إذا لم يتم إدخال ترتيب، نجعله في آخر الترتيب
        if (empty($data['sort_order'])) {
            $maxOrder = Banner::where('position', $data['position'])->max('sort_order');
            $data['sort_order'] = ($maxOrder ? $maxOrder + 1 : 1);
        }

        $banner = Banner::create($data);

        return response()->json([
            'status'  => true,
            'message' => 'تم إضافة البنر بنجاح',
            'banner'  => new BannerResource($banner),
        ], 201);
    }

    /**
     * عرض بنر محدد
     */
    public function show(Banner $banner)
    {
        return response()->json([
            'status'  => true,
            'message' => 'تم جلب بيانات البنر بنجاح',
            'banner'  => new BannerResource($banner),
        ]);
    }

    /**
     * تحديث بنر
     */
    public function update(UpdateBannerRequest $request, Banner $banner)
    {
        $banner->update($request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث بيانات البنر بنجاح',
            'banner'  => new BannerResource($banner->fresh()),
        ]);
    }

    /**
     * حذف بنر
     */
    public function destroy(Banner $banner)
    {
        $banner->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف البنر بنجاح',
        ]);
    }

    /**
     * تفعيل / إيقاف البنر
     */
    public function toggleActive(Banner $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);

        return response()->json([
            'status'    => true,
            'message'   => $banner->is_active ? 'تم تفعيل البنر بنجاح' : 'تم إيقاف البنر بنجاح',
            'banner'    => new BannerResource($banner->fresh()),
        ]);
    }

    /**
     * تحديث ترتيب ظهور البنرات دفعة واحدة أو بشكل فردي
     */
    public function reorder(Request $request)
    {
        $orders = $request->input('orders', []); // [[id => 1, sort_order => 1], ...]

        foreach ($orders as $item) {
            if (isset($item['id'], $item['sort_order'])) {
                Banner::where('id', $item['id'])->update(['sort_order' => (int) $item['sort_order']]);
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث ترتيب الظهور بنجاح',
        ]);
    }
}
