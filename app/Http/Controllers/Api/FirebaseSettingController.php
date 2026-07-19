<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseSettingController extends Controller
{
    public function getSettings()
    {
        return response()->json([
            'status' => true,
            'data' => [
                'firebase_enabled' => SystemSetting::getBoolean('firebase_enabled', false),
                'has_credentials' => Storage::disk('local')->exists('private/firebase/firebase-credentials.json'),
            ]
        ]);
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'firebase_enabled' => 'nullable|boolean',
            'credentials_file' => 'nullable|file|mimetypes:application/json,text/plain',
        ]);

        if ($request->has('firebase_enabled')) {
            SystemSetting::setValue('firebase_enabled', $request->firebase_enabled);
        }

        if ($request->hasFile('credentials_file')) {
            // Save securely in storage/app/private/firebase/
            $request->file('credentials_file')->storeAs('private/firebase', 'firebase-credentials.json', 'local');
        }

        return response()->json([
            'status' => true,
            'message' => 'تم حفظ إعدادات Firebase بنجاح',
        ]);
    }

    public function testNotification(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        try {
            $messaging = app('firebase.messaging');
            $message = CloudMessage::new()
                ->withNotification(Notification::create($request->title, $request->body))
                ->withChangedTarget('token', $request->fcm_token);

            $messaging->send($message);

            return response()->json([
                'status' => true,
                'message' => 'تم إرسال الإشعار التجريبي بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'فشل الإرسال: ' . $e->getMessage(),
            ], 500);
        }
    }
}
