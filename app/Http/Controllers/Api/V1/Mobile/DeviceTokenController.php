<?php

namespace App\Http\Controllers\Api\V1\Mobile;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * حفظ أو تحديث OneSignal Subscription ID (Player ID)
     * يُستدعى من التطبيق بعد تسجيل الدخول مباشرة
     */
    public function store(Request $request)
    {
        $request->validate([
            'onesignal_player_id' => 'required|string',
            'platform'            => 'nullable|string|in:ios,android,web',
            'device_name'         => 'nullable|string',
            'app_version'         => 'nullable|string',
        ], [
            'onesignal_player_id.required' => 'معرّف الجهاز في OneSignal مطلوب',
        ]);

        $user = $request->user();

        DeviceToken::updateOrCreate(
            ['onesignal_player_id' => $request->onesignal_player_id],
            [
                'tokenable_type'      => get_class($user),
                'tokenable_id'        => $user->id,
                'onesignal_player_id' => $request->onesignal_player_id,
                'platform'            => $request->platform,
                'device_name'         => $request->device_name,
                'app_version'         => $request->app_version,
                'last_used_at'        => now(),
                'is_active'           => true,
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'تم تسجيل الجهاز بنجاح',
        ]);
    }

    /**
     * حذف Subscription ID عند تسجيل الخروج
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'onesignal_player_id' => 'required|string',
        ]);

        $user = $request->user();

        DeviceToken::where('tokenable_type', get_class($user))
            ->where('tokenable_id', $user->id)
            ->where('onesignal_player_id', $request->onesignal_player_id)
            ->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم إلغاء تسجيل الجهاز بنجاح',
        ]);
    }
}
