<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use App\Services\Notifications\OneSignalService;

class OneSignalSettingController extends Controller
{
    public function getSettings()
    {
        return response()->json([
            'status' => true,
            'data' => [
                'onesignal_app_id' => SystemSetting::getValue('onesignal_app_id', ''),
                'onesignal_rest_api_key' => SystemSetting::getValue('onesignal_rest_api_key', ''),
                'onesignal_enabled' => SystemSetting::getBoolean('onesignal_enabled', true)
            ]
        ]);
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'onesignal_app_id' => 'nullable|string',
            'onesignal_rest_api_key' => 'nullable|string',
            'onesignal_enabled' => 'nullable|boolean',
        ]);

        SystemSetting::setValue('onesignal_app_id', $request->input('onesignal_app_id'));
        SystemSetting::setValue('onesignal_rest_api_key', $request->input('onesignal_rest_api_key'));
        
        if ($request->has('onesignal_enabled')) {
            SystemSetting::setValue('onesignal_enabled', $request->boolean('onesignal_enabled'));
        }

        return response()->json([
            'status' => true,
            'message' => 'تم حفظ إعدادات OneSignal بنجاح'
        ]);
    }

    public function testNotification(Request $request, OneSignalService $oneSignalService)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $success = $oneSignalService->broadcast($request->title, $request->body);

        if ($success) {
            return response()->json([
                'status' => true,
                'message' => 'تم الإرسال بنجاح، تحقق من أجهزتك.'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'فشل إرسال الإشعار. تأكد من صحة المفاتيح (App ID و API Key).'
        ], 500);
    }
}
