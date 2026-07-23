<?php

namespace App\Actions\Orders;

use App\DTOs\Orders\CreateOrderDTO;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\MedicalTest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\PackageOffer;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CreateOrderAction
{
    /**
     * تنفيذ إنشاء الطلب بالكامل في المعاملة وبناء الفاتورة وحفظ اللوجز
     */
    public function execute(CreateOrderDTO $dto): Order
    {
        // 1. حل بنود السلة وحساب الـ subtotal
        $resolvedItems = [];
        $subtotal      = 0.0;

        foreach ($dto->items as $raw) {
            if ($raw['item_type'] === 'test') {
                $model = MedicalTest::where('id', $raw['item_id'])->where('is_active', true)->first();
                if (!$model) {
                    throw new UnprocessableEntityHttpException("التحليل #{$raw['item_id']} غير موجود أو غير مفعل");
                }
                $price   = (float) $model->total_price;
                $nameAr  = $model->name_ar;
            } else {
                $model = PackageOffer::where('id', $raw['item_id'])->where('is_active', true)->first();
                if (!$model) {
                    throw new UnprocessableEntityHttpException("الباقة #{$raw['item_id']} غير موجودة أو غير مفعلة");
                }
                $price   = (float) ($model->discount_price ?? $model->original_price);
                $nameAr  = $model->name_ar;
            }
            $subtotal       += $price;
            $resolvedItems[] = [
                'item_type' => $raw['item_type'],
                'item_id'   => $raw['item_id'],
                'name_ar'   => $nameAr,
                'price'     => $price,
            ];
        }

        // 2. كلفة الخدمة وتحديد المنطقة الجغرافية
        $serviceFee = 0.0;
        $coverageZoneId = null;
        $coverageZoneSnapshot = null;

        if ($dto->lat && $dto->lng) {
            $engine = app(\App\Services\Coverage\Contracts\CoverageEngineInterface::class);
            $coverage = $engine->verifyCoverage($dto->lat, $dto->lng, $dto->user->id);
            
            if (!$coverage->isCovered) {
                throw new UnprocessableEntityHttpException($coverage->message ?? 'عذراً، الخدمة غير متوفرة في موقعك الحالي.');
            }
            
            $baseFee = (float) $coverage->fee;
            $threshold = $coverage->freeVisitThreshold;
            
            if ($threshold !== null && $threshold > 0 && $subtotal >= $threshold) {
                $serviceFee = 0.0;
            } else {
                $serviceFee = $baseFee;
            }

            $coverageZoneId = $coverage->zone?->id;
            $coverageZoneSnapshot = $coverage->zone ? json_encode($coverage->zone->toArray()) : null;
        } else {
            throw new UnprocessableEntityHttpException('يرجى تحديد موقعك على الخريطة لتحديد توفر الخدمة.');
        }

        // 3. حفظ الطلب في قاعدة البيانات ضمن transaction مع قفل صف الكوبون لمنع تجاوز الحد
        $order = DB::transaction(function () use ($dto, $resolvedItems, $subtotal, $serviceFee, $coverageZoneId, $coverageZoneSnapshot) {
            $couponId       = null;
            $discountAmount = 0.0;

            if ($dto->couponCode) {
                $coupon = Coupon::where('code', $dto->couponCode)->lockForUpdate()->first();

                if (!$coupon) {
                    throw new UnprocessableEntityHttpException('كود الخصم المدخل غير موجود');
                }

                if ($coupon->status !== 'active') {
                    $msg = match ($coupon->status) {
                        'inactive'      => 'هذا الكوبون موقوف حالياً',
                        'expired_time'  => 'انتهت صلاحية هذا الكوبون',
                        'expired_limit' => 'تم استنفاد استخدامات هذا الكوبون',
                        'upcoming'      => 'لم يبدأ وقت هذا الكوبون بعد',
                        default         => 'الكوبون غير صالح للاستخدام',
                    };
                    throw new UnprocessableEntityHttpException($msg);
                }

                $alreadyUsed = $dto->user->phone
                    ? CouponUsage::where('coupon_id', $coupon->id)->where('phone', $dto->user->phone)->exists()
                    : false;

                if ($alreadyUsed) {
                    throw new UnprocessableEntityHttpException('لقد قمت باستخدام هذا الكوبون مسبقاً في طلب آخر ولا يمكن تكراره');
                }

                $couponId       = $coupon->id;
                $discountAmount = $coupon->discount_type === 'percentage'
                    ? round($subtotal * ($coupon->discount_value / 100), 2)
                    : min($coupon->discount_value, $subtotal);
            }

            $total = max(0, $subtotal + $serviceFee - $discountAmount);

            $order = Order::create([
                'patient_id'      => $dto->user->id,
                'user_id'         => $dto->user->id,
                'coverage_zone_id'=> $coverageZoneId,
                'coverage_zone_snapshot' => $coverageZoneSnapshot,
                'coupon_id'       => $couponId,
                'status'          => 'pending',
                'subtotal'        => $subtotal,
                'service_fee'     => $serviceFee,
                'discount_amount' => $discountAmount,
                'total'           => $total,
                'visit_date'      => $dto->visitDate,
                'visit_time'      => $dto->visitTime,
                'visit_period'    => $dto->visitPeriod,
                'address_text'    => $dto->addressText,
                'lat'             => $dto->lat,
                'lng'             => $dto->lng,
                'building'        => $dto->building,
                'floor'           => $dto->floor,
                'apartment'       => $dto->apartment,
                'landmark'        => $dto->landmark,
                'doctor_name'     => $dto->doctorName,
                'referral_image'  => $dto->referralImage,
                'notes'           => $dto->notes,
            ]);

            foreach ($resolvedItems as $item) {
                OrderItem::create(array_merge(['order_id' => $order->id], $item));
            }

            if ($couponId) {
                CouponUsage::create([
                    'coupon_id'             => $couponId,
                    'patient_id'            => $dto->user->id,
                    'user_name'             => $dto->user->name ?: 'مراجع',
                    'phone'                 => $dto->user->phone,
                    'discount_amount'       => $discountAmount,
                    'total_before_discount' => $subtotal + $serviceFee,
                    'total_after_discount'  => $total,
                    'used_at'               => now(),
                ]);
                Coupon::whereKey($couponId)->increment('used_count');
            }

            OrderStatusLog::create([
                'order_id'           => $order->id,
                'from_status'        => null,
                'to_status'          => 'pending',
                'changed_by_user_id' => $dto->user->id,
                'notes'              => 'إنشاء الطلب من قبل المراجع عبر التطبيق',
            ]);

            return $order;
        });

        $order->load(['items', 'coupon']);

        event(new \App\Events\OrderCreated($order));
        event(new \App\Events\OrderStatusChanged($order, \App\Enums\NotificationType::PENDING));

        return $order;
    }
}
