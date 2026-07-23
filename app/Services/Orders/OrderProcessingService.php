<?php

namespace App\Services\Orders;

use App\Models\Coupon;
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
    public function calculateCartPreview(float $subtotal, ?string $couponCode, ?float $lat = null, ?float $lng = null, bool $hasLabItems = false): array
    {
        $deliveryFee = 0.0;
        $deliveryFeeLabel = 'أجور الزيارة المنزلية';
        $isFreeVisit = false;
        $remainingForFreeVisit = 0.0;

        if ($hasLabItems) {
            // إذا السلة تحتوي تحاليل، الموقع مطلوب إلزامياً
            if (!$lat || !$lng) {
                return [
                    'status'         => false,
                    'requires_location' => true,
                    'message'        => 'الرجاء تحديد موقعك على الخريطة لاحتساب أجور الزيارة المنزلية للتحاليل.',
                ];
            }

            $engine   = app(\App\Services\Coverage\Contracts\CoverageEngineInterface::class);
            $coverage = $engine->verifyCoverage($lat, $lng);

            if ($coverage->isCovered) {
                $baseFee = $coverage->fee ?? 0.0;
                $deliveryFeeLabel = 'أجور الزيارة المنزلية (' . ($coverage->zone?->name ?? 'منطقة مغطاة') . ')';
                $threshold = $coverage->freeVisitThreshold;

                if ($threshold !== null && $threshold > 0) {
                    if ($subtotal >= $threshold) {
                        $deliveryFee = 0.0;
                        $isFreeVisit = true;
                        $remainingForFreeVisit = 0.0;
                    } else {
                        $deliveryFee = $baseFee;
                        $isFreeVisit = false;
                        $remainingForFreeVisit = max(0, $threshold - $subtotal);
                    }
                } else {
                    $deliveryFee = $baseFee;
                    if ($deliveryFee <= 0) {
                        $isFreeVisit = true;
                    }
                }
            } else {
                return [
                    'status'  => false,
                    'covered' => false,
                    'message' => $coverage->message ?? 'عذراً، الخدمة غير متوفرة في موقعك الحالي.',
                ];
            }
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

        $total = max(0, $subtotal + $deliveryFee - $discountAmount);

        return [
            'status'         => true,
            'message'        => 'تم حساب ملخص الفاتورة بنجاح',
            'cart_breakdown' => [
                'subtotal'                   => $subtotal,
                'delivery_fee'               => $deliveryFee,
                'delivery_fee_label'         => $deliveryFeeLabel,
                'is_free_visit'              => $isFreeVisit,
                'remaining_for_free_visit'   => $remainingForFreeVisit,
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
