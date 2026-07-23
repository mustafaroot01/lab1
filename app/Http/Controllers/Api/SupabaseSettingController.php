<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SupabaseSettingController extends Controller
{
    /**
     * جلب إعدادات ربط Supabase
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'تم جلب إعدادات Supabase',
            'data' => [
                'supabase_anon_key' => SystemSetting::getValue('supabase_anon_key', ''),
                // We return the URL from config just to show it, but it's not editable here
                'supabase_url' => config('services.supabase.url'),
                'is_configured' => !empty(config('services.supabase.url')) && !empty(config('services.supabase.service_role_key')),
            ]
        ]);
    }

    /**
     * تحديث مفتاح Anon Key
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supabase_anon_key' => 'nullable|string',
        ]);

        if (array_key_exists('supabase_anon_key', $validated)) {
            SystemSetting::setValue('supabase_anon_key', $validated['supabase_anon_key']);
        }

        return response()->json([
            'status' => true,
            'message' => 'تم حفظ إعدادات Supabase بنجاح',
        ]);
    }

    /**
     * فحص الاتصال مع Supabase باستخدام Service Role Key
     */
    public function testConnection(): JsonResponse
    {
        $url = config('services.supabase.url');
        $key = config('services.supabase.service_role_key');

        if (empty($url) || empty($key)) {
            return response()->json([
                'status' => false,
                'message' => 'بيانات الاتصال (URL أو Service Key) غير متوفرة في السيرفر (.env).',
            ], 400);
        }

        try {
            // Test connection by fetching a health endpoint or just pinging REST API
            // PostgREST typically responds to root /rest/v1/ with a swagger JSON or similar if authorized.
            $response = Http::withHeaders([
                'apikey' => $key,
                'Authorization' => 'Bearer ' . $key,
            ])->timeout(5)->get(rtrim($url, '/') . '/rest/v1/');

            if ($response->successful()) {
                return response()->json([
                    'status' => true,
                    'message' => 'تم الاتصال بـ Supabase بنجاح!',
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'فشل الاتصال: ' . $response->body(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء الاتصال: ' . $e->getMessage(),
            ], 500);
        }
    }
}
