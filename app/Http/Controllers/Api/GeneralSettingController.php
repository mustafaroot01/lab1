<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGeneralSettingRequest;
use App\Http\Resources\GeneralSettingResource;
use App\Models\Setting;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeneralSettingController extends Controller
{
    /**
     * جلب الإعدادات العامّة للمختبر والنظام
     */
    public function index(): JsonResponse
    {
        $data = [
            'lab_name'              => Setting::get('lab_name', 'Healthy Lab - هيلثي لاب للتحاليل المخبرية'),
            'support_phone'         => Setting::get('support_phone', '07700000000'),
            'support_email'         => Setting::get('support_email', 'support@healthylab.iq'),
            'work_hours'            => Setting::get('work_hours', 'يومياً من الساعة 8:00 صباحاً حتى 10:00 مساءً'),
            'welcome_message'       => Setting::get('welcome_message', 'مرحباً بكم في تطبيق Healthy Lab للخدمات المخبرية والتحاليل المنزلية المتكاملة'),
            'package_offers_active' => SystemSetting::getBoolean('package_offers_active', true),
            'chat_active'           => SystemSetting::getBoolean('chat_active', true),
            'maintenance_mode'      => SystemSetting::getBoolean('maintenance_mode', false),
            'supabase_url'          => config('services.supabase.url'),
            'supabase_anon_key'     => SystemSetting::getValue('supabase_anon_key', ''),
        ];

        return response()->json([
            'status'  => true,
            'message' => 'تم جلب الإعدادات العامّة بنجاح',
            'data'    => new GeneralSettingResource($data),
        ]);
    }

    /**
     * تحديث الإعدادات العامّة وحفظها
     */
    public function update(UpdateGeneralSettingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (array_key_exists('lab_name', $validated)) {
            Setting::set('lab_name', trim((string) $validated['lab_name']));
        }
        if (array_key_exists('support_phone', $validated)) {
            Setting::set('support_phone', trim((string) $validated['support_phone']));
        }
        if (array_key_exists('support_email', $validated)) {
            Setting::set('support_email', trim((string) $validated['support_email']));
        }
        if (array_key_exists('work_hours', $validated)) {
            Setting::set('work_hours', trim((string) $validated['work_hours']));
        }
        if (array_key_exists('welcome_message', $validated)) {
            Setting::set('welcome_message', trim((string) $validated['welcome_message']));
        }
        if (array_key_exists('maintenance_mode', $validated)) {
            $boolVal = filter_var($validated['maintenance_mode'], FILTER_VALIDATE_BOOLEAN);
            SystemSetting::setValue('maintenance_mode', $boolVal);
            Setting::set('maintenance_mode', $boolVal);
        }
        if (array_key_exists('package_offers_active', $validated)) {
            $boolVal = filter_var($validated['package_offers_active'], FILTER_VALIDATE_BOOLEAN);
            SystemSetting::setValue('package_offers_active', $boolVal);
            Setting::set('package_offers_active', $boolVal);
        }
        if (array_key_exists('chat_active', $validated)) {
            $boolVal = filter_var($validated['chat_active'], FILTER_VALIDATE_BOOLEAN);
            SystemSetting::setValue('chat_active', $boolVal);
            Setting::set('chat_active', $boolVal);
        }

        $updatedData = [
            'lab_name'              => Setting::get('lab_name', 'Healthy Lab'),
            'support_phone'         => Setting::get('support_phone', ''),
            'support_email'         => Setting::get('support_email', ''),
            'work_hours'            => Setting::get('work_hours', ''),
            'welcome_message'       => Setting::get('welcome_message', ''),
            'package_offers_active' => SystemSetting::getBoolean('package_offers_active', true),
            'chat_active'           => SystemSetting::getBoolean('chat_active', true),
            'maintenance_mode'      => SystemSetting::getBoolean('maintenance_mode', false),
            'supabase_url'          => config('services.supabase.url'),
            'supabase_anon_key'     => SystemSetting::getValue('supabase_anon_key', ''),
        ];

        return response()->json([
            'status'  => true,
            'message' => 'تم حفظ الإعدادات العامّة للمختبر بنجاح',
            'data'    => new GeneralSettingResource($updatedData),
        ]);
    }
}
