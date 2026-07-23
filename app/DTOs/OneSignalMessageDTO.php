<?php

namespace App\DTOs;

class OneSignalMessageDTO
{
    /**
     * @param array  $externalIds  External IDs (patient IDs) — تُستخدم عبر setExternalUserId في Flutter
     * @param string $title        عنوان الإشعار
     * @param string $body         نص الإشعار
     * @param array  $data         بيانات إضافية (payload) مثل order_id و screen
     * @param string|null $url     رابط يُفتح عند الضغط على الإشعار
     * @param array  $playerIds    OneSignal Subscription/Player IDs مباشرة (أولوية على externalIds)
     */
    public function __construct(
        public array $externalIds,
        public string $title,
        public string $body,
        public array $data = [],
        public ?string $url = null,
        public array $playerIds = []   // Player IDs المحفوظة في device_tokens
    ) {}
}
