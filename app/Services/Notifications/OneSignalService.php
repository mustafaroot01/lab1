<?php

namespace App\Services\Notifications;

use App\DTOs\OneSignalMessageDTO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    protected string $appId;
    protected string $apiKey;
    protected string $apiUrl;
    protected bool $enabled;

    public function __construct()
    {
        $this->appId = \App\Models\SystemSetting::getValue('onesignal_app_id', config('services.onesignal.app_id', ''));
        $this->apiKey = \App\Models\SystemSetting::getValue('onesignal_rest_api_key', config('services.onesignal.rest_api_key', ''));
        $this->apiUrl = config('services.onesignal.api_url', 'https://onesignal.com/api/v1/notifications');
        
        $dbEnabled = \App\Models\SystemSetting::getBoolean('onesignal_enabled', true);
        $this->enabled = $dbEnabled && env('NOTIFICATIONS_ENABLED', true);
    }

    public function sendToUser(string $userId, string $title, string $body, array $data = [], ?string $url = null): bool
    {
        $dto = new OneSignalMessageDTO([$userId], $title, $body, $data, $url);
        return $this->send($dto);
    }

    public function sendToUsers(array $userIds, string $title, string $body, array $data = [], ?string $url = null): bool
    {
        $dto = new OneSignalMessageDTO($userIds, $title, $body, $data, $url);
        return $this->send($dto);
    }

    /**
     * إرسال إشعار مباشرة بـ OneSignal Player/Subscription IDs
     * أكثر دقة من external_id لأنه يستهدف الجهاز مباشرة
     */
    public function sendToPlayerIds(array $playerIds, string $title, string $body, array $data = [], ?string $url = null): bool
    {
        if (empty($playerIds)) return false;
        $dto = new OneSignalMessageDTO([], $title, $body, $data, $url);
        $dto->playerIds = $playerIds;
        return $this->send($dto);
    }

    public function broadcast(string $title, string $body, array $data = [], ?string $url = null): bool
    {
        return $this->send(new OneSignalMessageDTO([], $title, $body, $data, $url), true);
    }

    public function send(OneSignalMessageDTO $dto, bool $broadcast = false): bool
    {
        if (!$this->enabled) {
            Log::info("OneSignal Notifications are disabled via .env.");
            return true; // Pretend it succeeded
        }

        if (empty($this->appId) || empty($this->apiKey)) {
            Log::warning("OneSignal credentials not set.");
            return false;
        }

        $payload = [
            'app_id' => $this->appId,
            'target_channel' => 'push',
            'headings' => ['en' => $dto->title, 'ar' => $dto->title],
            'contents' => ['en' => $dto->body, 'ar' => $dto->body],
        ];

        if ($broadcast) {
            $payload['included_segments'] = ['Total Subscriptions'];
        } elseif (!empty($dto->playerIds)) {
            // أولوية لـ Player IDs المحفوظة في DB (أدق)
            $payload['include_subscription_ids'] = $dto->playerIds;
        } else {
            if (empty($dto->externalIds)) {
                return false;
            }
            // Fallback: external_id (patient->id) — يعتمد على OneSignal.login() في Flutter
            $payload['include_aliases'] = [
                'external_id' => $dto->externalIds
            ];
        }

        if (!empty($dto->data)) {
            $payload['data'] = $dto->data;
        }

        if (!empty($dto->url)) {
            $payload['url'] = $dto->url;
        }

        try {
            // OneSignal API expects Authorization: Basic YOUR_REST_API_KEY or Basic for old, Key for new.
            // Using Basic as it's the standard for the v1/notifications endpoint.
            $response = Http::withHeaders([
                    'Authorization' => 'Basic ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->timeout(10)
                ->post($this->apiUrl, $payload);

            if ($response->successful()) {
                return true;
            }

            Log::error("OneSignal API Error", [
                'status' => $response->status(),
                'response' => $response->json(),
                'payload' => $payload
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error("OneSignal Request Exception", [
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }
}
