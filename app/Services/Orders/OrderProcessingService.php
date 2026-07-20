<?php

namespace App\Services\Orders;

use App\Models\Coupon;
use App\Models\Branch;
use App\Models\District;
use Illuminate\Http\UploadedFile;

class OrderProcessingService
{
    /**
     * التحقق من صحة وحالة الكوبون وحساب السعر بعد الخصم
     */
    public function validateAndCalculateCoupon(string $code, float $subtotal): array
    {
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (!$coupon) {
            return ['status' => false, 'code' => 422, 'message' => 'الكوبون غير موجود'];
        }

        if ($coupon->status !== 'active') {
            $msg = match ($coupon->status) {
                'inactive'      => 'هذا الكوبون موقوف',
                'expired_time'  => 'انتهت صلاحية هذا الكوبون',
                'expired_limit' => 'تم استنفاد استخدامات هذا الكوبون',
                'upcoming'      => 'لم يبدأ وقت هذا الكوبون بعد',
                default         => 'الكوبون غير متاح',
            };
            return ['status' => false, 'code' => 422, 'message' => $msg];
        }

        $user = auth('sanctum')->user();
        if ($user && $user->phone) {
            $alreadyUsed = \App\Models\CouponUsage::where('coupon_id', $coupon->id)
                ->where('phone', $user->phone)
                ->exists();

            if ($alreadyUsed) {
                return ['status' => false, 'code' => 422, 'message' => 'لقد قمت باستخدام هذا الكوبون مسبقاً في طلب آخر ولا يمكن تكراره'];
            }
        }

        $discount = $coupon->discount_type === 'percentage'
            ? round($subtotal * ($coupon->discount_value / 100), 2)
            : min($coupon->discount_value, $subtotal);

        return [
            'status' => true,
            'code'   => 200,
            'data'   => [
                'status'          => true,
                'message'         => 'الكوبون صحيح ✓',
                'coupon_id'       => $coupon->id,
                'coupon_name'     => $coupon->name_ar,
                'discount_type'   => $coupon->discount_type,
                'discount_value'  => $coupon->discount_value,
                'discount_amount' => $discount,
                'subtotal'        => $subtotal,
                'total_after'     => max(0, $subtotal - $discount),
            ],
        ];
    }

    /**
     * حساب تفاصيل فاتورة السلة ومعاينة أجور الزيارة وتطبيق الخصم
     */
    public function calculateCartPreview(float $subtotal, ?int $branchId, ?string $couponCode, ?int $districtId = null): array
    {
        $serviceFee = 0.0;
        $serviceFeeLabel = 'أجور الزيارة المنزلية';
        $isFreeVisit = false;
        $freeThresholdTarget = 0.0;
        $amountLeftForFreeVisit = 0.0;

        if ($districtId) {
            $district = District::with('branch')->find($districtId);
            if ($district) {
                $serviceFee = $district->branch ? (float) $district->branch->service_fee : 0.0;
                $serviceFeeLabel = 'أجور الزيارة المنزلية (' . $district->name . ')';
                $freeThresholdTarget = $district->branch ? (float) $district->branch->free_threshold : 0.0;

                if ($freeThresholdTarget > 0 && $subtotal >= $freeThresholdTarget) {
                    $serviceFee = 0.0;
                    $isFreeVisit = true;
                } elseif ($freeThresholdTarget > 0 && $subtotal < $freeThresholdTarget) {
                    $amountLeftForFreeVisit = round($freeThresholdTarget - $subtotal, 2);
                }
            }
        } elseif ($branchId) {
            $branch = Branch::find($branchId);
            if ($branch) {
                $serviceFee = (float) ($branch->service_fee ?? 0);
                $serviceFeeLabel = 'أجور الزيارة المنزلية (' . $branch->name_ar . ')';
                $freeThresholdTarget = (float) ($branch->free_threshold ?? 0);

                if ($freeThresholdTarget > 0 && $subtotal >= $freeThresholdTarget) {
                    $serviceFee = 0.0;
                    $isFreeVisit = true;
                } elseif ($freeThresholdTarget > 0 && $subtotal < $freeThresholdTarget) {
                    $amountLeftForFreeVisit = round($freeThresholdTarget - $subtotal, 2);
                }
            }
        }

        if ($serviceFee <= 0) {
            $isFreeVisit = true;
        }

        $hasCoupon = false;
        $discountAmount = 0.0;
        $validCouponCode = null;

        if (!empty($couponCode)) {
            $coupon = Coupon::where('code', strtoupper($couponCode))->first();
            if ($coupon && $coupon->status === 'active') {
                $user = auth('sanctum')->user();
                $alreadyUsed = ($user && $user->phone)
                    ? \App\Models\CouponUsage::where('coupon_id', $coupon->id)->where('phone', $user->phone)->exists()
                    : false;

                if (!$alreadyUsed) {
                    $hasCoupon = true;
                    $validCouponCode = $coupon->code;
                    $discountAmount = $coupon->discount_type === 'percentage'
                        ? round($subtotal * ($coupon->discount_value / 100), 2)
                        : min($coupon->discount_value, $subtotal);
                }
            }
        }

        $total = max(0, $subtotal + $serviceFee - $discountAmount);

        return [
            'status'         => true,
            'message'        => 'تم حساب ملخص الفاتورة بنجاح',
            'cart_breakdown' => [
                'subtotal'                   => $subtotal,
                'service_fee'                => $serviceFee,
                'service_fee_label'          => $serviceFeeLabel,
                'is_free_visit'              => $isFreeVisit,
                'free_threshold_target'      => $freeThresholdTarget,
                'amount_left_for_free_visit' => $amountLeftForFreeVisit,
                'has_coupon'                 => $hasCoupon,
                'coupon_code'                => $validCouponCode,
                'discount_amount'            => $discountAmount,
                'total'                      => $total,
            ],
        ];
    }

    /**
     * حفظ المرفق والصورة بمسار آمن وإرجاع مسارها
     */
    public function storeReferralImage(UploadedFile $file): array
    {
        $path = $file->store('referrals', 'public');

        return [
            'status'  => true,
            'message' => 'تم رفع صورة الراجعة بنجاح',
            'path'    => $path,
            'url'     => asset('storage/' . $path),
        ];
    }
}
