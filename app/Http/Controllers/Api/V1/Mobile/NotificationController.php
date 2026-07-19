<?php

namespace App\Http\Controllers\Api\V1\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * جلب سجل الإشعارات للمستخدم الحالي
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = AppNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => true,
            'message' => 'تم جلب الإشعارات بنجاح',
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
            ]
        ]);
    }

    /**
     * تحديد الإشعارات كمقروءة
     */
    public function markAsRead(Request $request)
    {
        $user = $request->user();

        AppNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => true,
            'message' => 'تم تحديد جميع الإشعارات كمقروءة',
        ]);
    }
}
