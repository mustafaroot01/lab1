<?php

namespace App\Http\Controllers\Api\V1\Mobile;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * Store or Update FCM Device Token
     */
    public function store(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'platform' => 'nullable|string|in:ios,android,web',
            'device_name' => 'nullable|string',
            'app_version' => 'nullable|string',
        ]);

        $user = $request->user();

        DeviceToken::updateOrCreate(
            ['fcm_token' => $request->fcm_token],
            [
                'tokenable_type' => get_class($user),
                'tokenable_id' => $user->id,
                'platform' => $request->platform,
                'device_name' => $request->device_name,
                'app_version' => $request->app_version,
                'last_used_at' => now(),
                'is_active' => true,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'تم حفظ رمز الجهاز بنجاح',
        ]);
    }

    /**
     * Delete FCM Device Token on Logout
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();

        DeviceToken::where('tokenable_type', get_class($user))
            ->where('tokenable_id', $user->id)
            ->where('fcm_token', $request->fcm_token)
            ->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف رمز الجهاز بنجاح',
        ]);
    }
}
