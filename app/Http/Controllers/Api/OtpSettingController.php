<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOtpSettingRequest;
use App\Models\Setting;
use App\Services\OtpService;
use Illuminate\Http\Request;

class OtpSettingController extends Controller
{
    /**
     * جلب إعدادات الـ OTP الحالية
     */
    public function index()
    {
        $provider  = Setting::get('otp_provider', env('OTP_PROVIDER', 'otpiq'));
        $otpiqKey  = Setting::get('otpiq_api_key', env('OTPIQ_API_KEY', ''));
        $arqamKey  = Setting::get('arqam_api_key', env('ARQAM_API_KEY', ''));

        $activeKey = ($provider === 'arqam') ? $arqamKey : $otpiqKey;

        return response()->json([
            'status'        => true,
            'message'       => 'تم جلب إعدادات OTP بنجاح',
            'otp_provider'  => $provider,
            'otpiq_api_key' => $otpiqKey,
            'arqam_api_key' => $arqamKey,
            'is_configured' => !empty($activeKey),
        ]);
    }

    /**
     * تحديث إعدادات الـ API وحفظها في قاعدة البيانات
     */
    public function update(UpdateOtpSettingRequest $request)
    {
        $provider = trim($request->input('otp_provider', Setting::get('otp_provider', 'otpiq')));

        if (!empty($provider)) {
            Setting::set('otp_provider', $provider);
        }
        if ($request->has('otpiq_api_key')) {
            Setting::set('otpiq_api_key', trim($request->input('otpiq_api_key', '')));
        }
        if ($request->has('arqam_api_key')) {
            Setting::set('arqam_api_key', trim($request->input('arqam_api_key', '')));
        }

        $activeKey = ($provider === 'arqam')
            ? Setting::get('arqam_api_key', '')
            : Setting::get('otpiq_api_key', '');

        return response()->json([
            'status'        => true,
            'message'       => 'تم حفظ الإعدادات وتفعيل خدمة (' . strtoupper($provider) . ') بنجاح.',
            'otp_provider'  => $provider,
            'otpiq_api_key' => Setting::get('otpiq_api_key', ''),
            'arqam_api_key' => Setting::get('arqam_api_key', ''),
            'is_configured' => !empty($activeKey),
        ]);
    }

    /**
     * اختبار إرسال رسالة تحقق لواتساب رقم تجريبي
     */
    public function testSend(Request $request, OtpService $otpService)
    {
        $phone = $request->input('phone');

        if (empty($phone)) {
            return response()->json([
                'status'  => false,
                'message' => 'يرجى إدخال رقم الهاتف لاختبار الإرسال',
            ], 422);
        }

        $result = $otpService->sendOtp($phone);

        return response()->json([
            'status'  => $result['success'],
            'message' => $result['success']
                ? 'تم إرسال رسالة الاختبار بنجاح عبر الواتساب إلى رقم الهاتف!'
                : ($result['message'] ?? 'فشل إرسال رسالة الاختبار، يرجى التأكد من صحة المفتاح والرصيد'),
            'details' => $result,
        ]);
    }
}

