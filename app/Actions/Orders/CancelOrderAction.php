<?php

namespace App\Actions\Orders;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CancelOrderAction
{
    /**
     * إلغاء الطلب المبدئي وتسجيل السبب في اللوج واسترجاع الكوبون إن وجد
     */
    public function execute(Order $order, $user, ?string $cancelReason = null): Order
    {
        if ($order->status !== 'pending') {
            throw new UnprocessableEntityHttpException('لا يمكن إلغاء الطلب بعد تأكيده من قبل الفريق');
        }

        $reason = $cancelReason ?? 'طلب إلغاء من المريض';

        return DB::transaction(function () use ($order, $user, $reason) {
            $order->update([
                'status'        => 'cancelled',
                'cancel_reason' => $reason,
            ]);

            OrderStatusLog::create([
                'order_id'           => $order->id,
                'from_status'        => 'pending',
                'to_status'          => 'cancelled',
                'changed_by_user_id' => $user->id,
                'notes'              => $reason,
            ]);

            if ($order->coupon_id) {
                Coupon::whereKey($order->coupon_id)->where('used_count', '>', 0)->decrement('used_count');
                CouponUsage::where('coupon_id', $order->coupon_id)
                           ->where('patient_id', $order->patient_id ?: $order->user_id)
                           ->delete();
            }

            return $order;
        });

        event(new \App\Events\OrderStatusChanged($order, \App\Enums\NotificationType::CANCELLED));

        return $order;
    }
}
