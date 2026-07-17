<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Http\Requests\RecordCouponUsageRequest;
use App\Http\Resources\CouponResource;
use App\Http\Resources\CouponUsageResource;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::withCount('usages');

        // Search
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->where('code', 'like', "%{$q}%")
                         ->orWhere('name_ar', 'like', "%{$q}%")
                         ->orWhere('name_en', 'like', "%{$q}%");
            });
        }

        // Status filter — فلترة على مستوى قاعدة البيانات بدلاً من جلب كل السجلات للذاكرة
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = $request->input('status');
            $this->applyCouponStatusFilter($query, $status);
        }

        // Sorting
        $sortBy = $request->input('sortBy', 'id');
        if (is_array($sortBy) && isset($sortBy[0]['key'])) {
            $orderBy = $sortBy[0]['order'] ?? 'desc';
            $sortBy = $sortBy[0]['key'];
        } else {
            $orderBy = $request->input('orderBy', 'desc');
        }

        if (in_array($sortBy, ['id', 'code', 'name_ar', 'discount_value', 'used_count', 'start_date', 'end_date'])) {
            $query->orderBy($sortBy, $orderBy === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $totalCouponsCount = $query->count();

        // Pagination
        $itemsPerPage = (int) $request->input('itemsPerPage', 10);
        if ($itemsPerPage === -1) {
            $itemsPerPage = $totalCouponsCount > 0 ? $totalCouponsCount : 10;
        }

        $coupons = $query->paginate($itemsPerPage);

        // Summary statistics — استعلام واحد فعال بدلاً من Coupon::all()
        $summary = $this->buildCouponSummary();

        return response()->json([
            'status' => true,
            'message' => 'تم جلب قائمة الكوبونات بنجاح',
            'coupons' => CouponResource::collection($coupons),
            'totalCoupons' => $totalCouponsCount,
            'summary' => $summary,
        ]);
    }

    /**
     * تطبيق فلتر حالة الكوبون على مستوى قاعدة البيانات
     * بدلاً من جلب كل السجلات وفلترتها في PHP
     */
    private function applyCouponStatusFilter($query, string $status): void
    {
        $now = Carbon::now();

        match ($status) {
            'active' => $query
                ->where('is_active', true)
                ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>', $now))
                ->where(fn($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
                ->where(fn($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $now)),

            'inactive' => $query->where('is_active', false),

            'expired_time' => $query
                ->where('is_active', true)
                ->whereNotNull('end_date')
                ->where('end_date', '<=', $now),

            'expired_limit' => $query
                ->where('is_active', true)
                ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>', $now))
                ->whereNotNull('usage_limit')
                ->whereColumn('used_count', '>=', 'usage_limit'),

            'upcoming' => $query
                ->where('is_active', true)
                ->whereNotNull('start_date')
                ->where('start_date', '>', $now),

            default => null,
        };
    }

    /**
     * بناء ملخص إحصائيات الكوبونات باستعلامات فعالة على قاعدة البيانات
     */
    private function buildCouponSummary(): array
    {
        $now = Carbon::now();

        return [
            'totalCoupons'   => Coupon::count(),
            'activeCoupons'  => Coupon::where('is_active', true)
                ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>', $now))
                ->where(fn($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
                ->where(fn($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $now))
                ->count(),
            'expiredCoupons' => Coupon::where('is_active', true)
                ->where(function ($q) use ($now) {
                    $q->where(fn($sub) => $sub->whereNotNull('end_date')->where('end_date', '<=', $now))
                      ->orWhere(fn($sub) => $sub->whereNotNull('usage_limit')->whereColumn('used_count', '>=', 'usage_limit'));
                })->count(),
            'totalUsages'    => (int) Coupon::sum('used_count'),
        ];
    }

    public function store(StoreCouponRequest $request)
    {
        $coupon = Coupon::create($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'تم إضافة الكوبون بنجاح',
            'coupon' => new CouponResource($coupon),
        ], 201);
    }

    public function show($id, Request $request)
    {
        $coupon = Coupon::findOrFail($id);

        $query = $coupon->usages();

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->where('user_name', 'like', "%{$q}%")
                         ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $sortBy = $request->input('sortBy', 'id');
        if (is_array($sortBy) && isset($sortBy[0]['key'])) {
            $sortBy = $sortBy[0]['key'];
            $orderBy = $sortBy[0]['order'] ?? 'desc';
        } else {
            $orderBy = $request->input('orderBy', 'desc');
        }

        if (in_array($sortBy, ['id', 'user_name', 'phone', 'discount_amount', 'total_after_discount', 'used_at'])) {
            $query->orderBy($sortBy, $orderBy === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('used_at', 'desc');
        }

        $totalUsagesCount = $query->count();
        $itemsPerPage = (int) $request->input('itemsPerPage', 10);
        if ($itemsPerPage === -1) {
            $itemsPerPage = $totalUsagesCount > 0 ? $totalUsagesCount : 10;
        }

        $usages = $query->paginate($itemsPerPage);

        return response()->json([
            'status' => true,
            'message' => 'تم جلب تفاصيل الكوبون وسجل الاستخدام',
            'coupon' => new CouponResource($coupon),
            'usages' => CouponUsageResource::collection($usages),
            'totalUsages' => $totalUsagesCount,
        ]);
    }

    public function update(UpdateCouponRequest $request, $id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث بيانات الكوبون بنجاح',
            'coupon' => new CouponResource($coupon),
        ]);
    }

    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف الكوبون بنجاح',
        ]);
    }

    public function toggleStatus(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => $request->boolean('is_active')]);

        return response()->json([
            'status' => true,
            'message' => $coupon->is_active ? 'تم تفعيل الكوبون بنجاح' : 'تم إيقاف الكوبون مؤقتاً',
            'coupon' => new CouponResource($coupon),
        ]);
    }

    public function recordUsage(RecordCouponUsageRequest $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        if ($coupon->status !== 'active') {
            return response()->json([
                'status' => false,
                'message' => 'هذا الكوبون غير متاح للاستخدام حالياً (الحالة: ' . $coupon->status . ')',
            ], 422);
        }

        $validated = $request->validated();
        $before = (float) $validated['total_before_discount'];
        $discountAmount = 0;

        if ($coupon->discount_type === 'percentage') {
            $discountAmount = ($before * $coupon->discount_value) / 100;
        } else {
            $discountAmount = $coupon->discount_value;
        }

        if ($discountAmount > $before) {
            $discountAmount = $before;
        }

        $after = $before - $discountAmount;

        $usage = CouponUsage::create([
            'coupon_id' => $coupon->id,
            'patient_id' => $request->user('sanctum')?->id ?? ($request->user('patient')?->id ?? null),
            'user_name' => $validated['user_name'],
            'phone' => $validated['phone'] ?? null,
            'discount_amount' => $discountAmount,
            'total_before_discount' => $before,
            'total_after_discount' => $after,
            'used_at' => Carbon::now(),
        ]);

        $coupon->increment('used_count');

        return response()->json([
            'status' => true,
            'message' => 'تم تطبيق وحفظ استخدام الكوبون بنجاح',
            'usage' => new CouponUsageResource($usage),
            'coupon' => new CouponResource($coupon->fresh(['usages'])),
        ]);
    }
}
