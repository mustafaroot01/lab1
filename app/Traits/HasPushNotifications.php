<?php

namespace App\Traits;

use App\Models\DeviceToken;

trait HasPushNotifications
{
    /**
     * OneSignal external_id — يساوي patient->id
     * يُستخدم لاستهداف المستخدم عبر OneSignal setExternalUserId في Flutter
     */
    public function routeNotificationForOneSignal(): string
    {
        return (string) $this->id;
    }

    /**
     * جلب Player IDs (Subscription IDs) من جدول device_tokens
     * يُستخدم كبديل أو تكملة لـ external_id عند إرسال إشعارات مباشرة
     */
    public function getActiveOneSignalPlayerIds(): array
    {
        return DeviceToken::where('tokenable_type', get_class($this))
            ->where('tokenable_id', $this->id)
            ->where('is_active', true)
            ->whereNotNull('onesignal_player_id')
            ->pluck('onesignal_player_id')
            ->toArray();
    }
}
