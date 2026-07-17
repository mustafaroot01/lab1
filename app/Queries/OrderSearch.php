<?php

namespace App\Queries;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrderSearch
{
    /**
     * بناء وتصفية استعلام قائمة الطلبات للوحة التحكم وتطبيقات الموبايل
     */
    public static function run(Request $request): Builder
    {
        $search   = trim($request->get('search') ?: $request->get('q', ''));
        $status   = $request->get('status');
        $branchId = $request->get('branch_id');
        $date     = $request->get('visit_date') ?: $request->get('date');

        return Order::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->whereHas('patient', function ($u) use ($search) {
                        $u->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('phone', 'LIKE', "%{$search}%");
                    });
                    if (is_numeric($search)) {
                        $sub->orWhere('id', (int) $search);
                    }
                });
            })
            ->when($status && $status !== 'all', fn($q) => $q->where('status', $status))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($date, fn($q) => $q->whereDate('visit_date', $date));
    }
}
