<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * Normalize Iraqi phone numbers to +964 format
     */
    public static function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/^0/', '', trim($phone));
        if (!str_starts_with($phone, '+')) {
            $phone = (!str_starts_with($phone, '964')) ? '+964' . $phone : '+' . $phone;
        }

        return $phone;
    }

    /**
     * إرسال رمز التحقق (سواء كان أرقام أو OTPIQ حسب السويتش المختار في الإعدادات)
     */
    public function sendOtp(string $phone): array
    {
        try {
            $phone    = self::normalizePhone($phone);
            $provider = Setting::get('otp_provider', env('OTP_PROVIDER', 'otpiq'));

            if ($provider === 'arqam') {
                return $this->sendViaArqamDirect($phone);
            }

            return $this->sendViaOtpiqDirect($phone);
        } catch (\Exception $e) {
            Log::error('OtpService::sendOtp: ' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 1. الربط المباشر مع سيرفر أرقام (Arqam Direct Connection)
     * سيرفر أرقام يولد الرقم عنده ويرسل واتساب ونستلم منه messageId
     */
    private function sendViaArqamDirect(string $phone): array
    {
        $apiKey = Setting::get('arqam_api_key', env('ARQAM_API_KEY', ''));
        if (empty($apiKey)) {
            return ['success' => false, 'message' => 'مفتاح خدمة أرقام (Arqam API Key) غير مهيأ في الإعدادات.'];
        }

        $response = Http::timeout(15)->retry(2, 300)
            ->withHeaders([
                'X-API-Key'    => $apiKey,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])
            ->post('https://otp.arqam.tech/api/sms/otp', [
                'phoneNumber' => $phone,
            ]);

        if (!$response->successful()) {
            Log::error('Arqam Direct Send failed: ' . $response->status() . ' — ' . $response->body());
            $errorData = $response->json();

            return [
                'success' => false,
                'message' => $errorData['message'] ?? 'تعذر إرسال رمز التحقق عبر أرقام، تأكد من المفتاح ورصيد حسابك.',
            ];
        }

        $data = $response->json();
        if (!empty($data['success']) && !empty($data['messageId'])) {
            // حفظ الـ messageId في الكاش لربطه برقم الهاتف عند التحقق
            Cache::put('arqam_msg_' . $phone, $data['messageId'], now()->addMinutes(10));

            return ['success' => true, 'messageId' => $data['messageId'], 'provider' => 'arqam'];
        }

        return ['success' => false, 'message' => $data['message'] ?? 'فشل استلام الـ messageId من سيرفر أرقام.'];
    }

    /**
     * 2. الربط المباشر مع سيرفر OTPIQ (OTPIQ Direct Connection)
     * نولد الرمز محلياً، نرسله لواتساب عبر سيرفر OTPIQ، ونحفظه في الكاش للتحقق
     */
    private function sendViaOtpiqDirect(string $phone): array
    {
        $apiKey = Setting::get('otpiq_api_key', env('OTPIQ_API_KEY', ''));
        if (empty($apiKey)) {
            return ['success' => false, 'message' => 'مفتاح خدمة OTPIQ (OTPIQ API Key) غير مهيأ في الإعدادات.'];
        }

        $code = (string) random_int(100000, 999999);

        $response = Http::timeout(15)->retry(2, 300)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept'        => 'application/json',
            ])
            ->post('https://api.otpiq.com/api/sms', [
                'phoneNumber'      => ltrim($phone, '+'),
                'smsType'          => 'verification',
                'verificationCode' => $code,
                'provider'         => 'whatsapp',
            ]);

        if (!$response->successful()) {
            Log::error('OTPIQ Direct Send failed: ' . $response->status() . ' — ' . $response->body());
            $errorData = $response->json();

            return [
                'success' => false,
                'message' => $errorData['error'] ?? $errorData['message'] ?? 'تعذر إرسال رمز التحقق عبر OTPIQ، تأكد من المفتاح ورصيد حسابك.',
            ];
        }

        Cache::put('otpiq_' . $phone, $code, now()->addMinutes(5));
        Cache::forget('otpiq_attempts_' . $phone);

        return ['success' => true, 'messageId' => $phone, 'provider' => 'otpiq'];
    }

    /**
     * التحقق المباشر من الرمز (سواء مع سيرفر أرقام أو مع الكاش لخدمة OTPIQ)
     */
    public function verifyOtp(string $phoneOrMessageId, string $code): array
    {
        $phone    = self::normalizePhone($phoneOrMessageId);
        $provider = Setting::get('otp_provider', env('OTP_PROVIDER', 'otpiq'));

        // وضع التطوير السريع للبيئة المحلية فقط
        if (app()->environment('local') && trim($code) === '1234') {
            return ['success' => true, 'message' => 'تم التحقق بنجاح (وضع التطوير)'];
        }

        if ($provider === 'arqam') {
            return $this->verifyViaArqamDirect($phone, $code);
        }

        return $this->verifyViaOtpiqDirect($phone, $code);
    }

    /**
     * التحقق المباشر من سيرفر أرقام (Arqam Native Verify API)
     */
    private function verifyViaArqamDirect(string $phone, string $code): array
    {
        $apiKey    = Setting::get('arqam_api_key', env('ARQAM_API_KEY', ''));
        $messageId = Cache::get('arqam_msg_' . $phone, $phone);

        $response = Http::timeout(15)->retry(2, 300)
            ->withHeaders([
                'X-API-Key'    => $apiKey,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])
            ->post('https://otp.arqam.tech/api/sms/verify', [
                'messageId' => $messageId,
                'code'      => trim($code),
            ]);

        $data = $response->json();

        if ($response->successful() && !empty($data['verified'])) {
            Cache::forget('arqam_msg_' . $phone);

            return ['success' => true, 'message' => $data['message'] ?? 'تم التحقق بنجاح من سيرفر أرقام'];
        }

        return ['success' => false, 'message' => $data['message'] ?? 'رمز التحقق غير صحيح أو انتهت صلاحيته من سيرفر أرقام.'];
    }

    /**
     * التحقق المباشر لخدمة OTPIQ (Local Cache Verify)
     */
    private function verifyViaOtpiqDirect(string $phone, string $code): array
    {
        $cacheKey = 'otpiq_' . $phone;
        $stored   = Cache::get($cacheKey);

        if (!$stored) {
            return ['success' => false, 'message' => 'انتهت صلاحية رمز التحقق أو لم يُطلب بعد، يرجى طلب رمز جديد.'];
        }

        $attemptsKey = 'otpiq_attempts_' . $phone;
        $attempts    = (int) Cache::get($attemptsKey, 0);

        if ($attempts >= 3) {
            Cache::forget($cacheKey);
            Cache::forget($attemptsKey);

            return ['success' => false, 'message' => 'تم إبطال رمز التحقق لتجاوز عدد المحاولات المسموح بها.'];
        }

        if (trim($stored) !== trim($code)) {
            Cache::put($attemptsKey, $attempts + 1, now()->addMinutes(5));

            return ['success' => false, 'message' => 'رمز التحقق المدخل غير صحيح (' . (2 - $attempts) . ' محاولات متبقية).'];
        }

        Cache::forget($cacheKey);
        Cache::forget($attemptsKey);

        return ['success' => true, 'message' => 'تم التحقق بنجاح'];
    }

    /**
     * التتبع المباشر لحالة الرسالة
     */
    public function getMessageHistory(string $messageId): array
    {
        $provider = Setting::get('otp_provider', env('OTP_PROVIDER', 'otpiq'));

        if ($provider === 'arqam') {
            $apiKey = Setting::get('arqam_api_key', env('ARQAM_API_KEY', ''));
            $response = Http::timeout(10)
                ->withHeaders(['X-API-Key' => $apiKey, 'Accept' => 'application/json'])
                ->get('https://otp.arqam.tech/api/messages/' . $messageId . '/history');

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }
        }

        return ['success' => true, 'message' => 'التتبع المباشر متاح لحسّاب أرقام فقط'];
    }
}
