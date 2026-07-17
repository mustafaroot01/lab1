<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * هل يمكن للمستخدم عرض هذا الطلب؟ (صاحب الطلب أو المشرف أو الفني المخصص)
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($order->user_id === $user->id) {
            return true;
        }

        if ($order->technician_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * هل يمكن للمستخدم إلغاء هذا الطلب؟ (المريض إذا كان الطلب مبدئياً)
     */
    public function cancel(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $order->user_id === $user->id && $order->status === 'pending';
    }

    /**
     * هل يمكن للمستخدم تحديث هذا الطلب؟ (المشرف فقط أو الفني في الطريق/سحب العينة)
     */
    public function update(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $order->technician_id === $user->id && in_array($order->status, ['on_the_way', 'sample_collected']);
    }
}
