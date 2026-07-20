<?php

namespace App\Http\Controllers\Api\V1\Patient\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\Patient;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class OtpAuthController extends Controller
{
    /**
     * طلب رمز التحقق OTP (نافذة موحدة لتسجيل الدخول / إنشاء حساب جديد)
     */
    public function requestOtp(RequestOtpRequest $request, OtpService $otpService)
    {
        $phone = OtpService::normalizePhone($request->input('phone'));
        $agreedToTerms = $request->boolean('agreed_to_terms');

        if (!$agreedToTerms) {
            return response()->json([
                'status'  => false,
                'message' => 'يجب الموافقة على شروط الخدمة وسياسة الخصوصية للمتابعة',
            ], 422);
        }

        $user = Patient::where('phone', $phone)->first();
        $isNewUser = false;

        if (!$user) {
            $user = Patient::create([
                'phone'                => $phone,
                'name'                 => 'زبون جديد',
                'agreed_to_terms'      => true,
                'is_profile_completed' => false,
            ]);
            $isNewUser = true;
        } else {
            $user->update(['agreed_to_terms' => true]);
        }

        // إرسال الرمز عبر خدمة OtpService
        $otpResult = $otpService->sendOtp($phone);

        // وضع التطوير السريع في بيئة local
        $devCode = null;
        if (app()->environment('local')) {
            $devCode = '1234';
            Cache::put('otp_' . $phone, $devCode, now()->addMinutes(10));
        }

        if (!app()->environment('local') && !($otpResult['success'] ?? false)) {
            return response()->json([
                'status'  => false,
                'message' => $otpResult['message'] ?? 'تعذّر إرسال رمز التحقق حالياً، يرجى المحاولة لاحقاً.',
            ], 503);
        }

        return response()->json([
            'status'               => true,
            'message'              => ($otpResult['success'] ?? false)
                ? 'تم إرسال رمز التحقق بنجاح عبر الواتساب'
                : 'تم إنشاء رمز التحقق (وضع التطوير)',
            'phone'                => $phone,
            'is_new_user'          => $isNewUser,
            'is_profile_completed' => (bool) $user->is_profile_completed,
            'otp_dev'              => $devCode,
        ]);
    }

    /**
     * التحقق من رمز OTP وإصدار توكنات Sanctum
     */
    public function verifyOtp(VerifyOtpRequest $request, OtpService $otpService)
    {
        $phone = OtpService::normalizePhone($request->input('phone'));
        $otpCode = trim($request->input('otp_code'));

        $user = Patient::where('phone', $phone)->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'المستخدم غير موجود، يرجى طلب رمز جديد',
            ], 404);
        }

        $verifyResult = $otpService->verifyOtp($phone, $otpCode);
        $isDevMasterCode = app()->environment('local') && $otpCode === '1234';

        if (!($verifyResult['success'] ?? false) && !$isDevMasterCode) {
            return response()->json([
                'status'  => false,
                'message' => $verifyResult['message'] ?? 'رمز التحقق المدخل غير صحيح',
            ], 422);
        }

        if (!$user->is_active) {
            return response()->json([
                'status'  => false,
                'message' => 'تم إيقاف حسابك من قبل الإدارة، لا يمكنك تسجيل الدخول للتطبيق.',
            ], 403);
        }

        $user->update([
            'otp_code'       => null,
            'otp_expires_at' => null,
        ]);

        $accessToken  = $user->createToken('MobileAppAccessToken', ['access'])->plainTextToken;
        $refreshToken = $user->createToken('MobileAppRefreshToken', ['refresh'])->plainTextToken;

        return response()->json([
            'status'               => true,
            'message'              => 'تم التحقق بنجاح',
            'token'                => $accessToken,
            'access_token'         => $accessToken,
            'refresh_token'        => $refreshToken,
            'token_type'           => 'Bearer',
            'expires_in'           => 2592000,
            'is_profile_completed' => (bool) $user->is_profile_completed,
            'next_step'            => $user->is_profile_completed ? 'home' : 'complete_profile',
            'user'                 => new UserResource($user->load(['district.branch'])),
        ]);
    }

    /**
     * تجديد توكن الدخول
     */
    public function refreshToken(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user || !$user->is_active) {
            if ($user) {
                $user->tokens()->delete();
            }
            return response()->json([
                'status'  => false,
                'message' => 'تم إيقاف حسابك أو انتهاء الجلسة، لا يمكن تجديد التوكن.',
            ], 403);
        }

        $request->user()->currentAccessToken()?->delete();

        $accessToken  = $user->createToken('MobileAppAccessToken', ['access'])->plainTextToken;
        $refreshToken = $user->createToken('MobileAppRefreshToken', ['refresh'])->plainTextToken;

        return response()->json([
            'status'        => true,
            'message'       => 'تم تجديد التوكن بنجاح',
            'token'         => $accessToken,
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => 2592000,
        ]);
    }

    /**
     * تسجيل الخروج
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        
        if ($user) {
            $user->update(['fcm_token' => null]);
            $user->currentAccessToken()?->delete();
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }
}
