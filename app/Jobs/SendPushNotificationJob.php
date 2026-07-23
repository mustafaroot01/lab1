<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\Notifications\OneSignalService;
use App\Models\NotificationLog;

class SendPushNotificationJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public $notifiable,
        public string $title,
        public string $body,
        public string $type,
        public array $payload = []
    ) {}

    public function handle(OneSignalService $oneSignalService): void
    {
        $success = false;

        // ─── استراتيجية الإرسال ──────────────────────────────────────────────────
        // الأولوية 1: استخدام OneSignal Player IDs المحفوظة في جدول device_tokens
        // الأولوية 2: استخدام external_id = patient->id (يعتمد على setExternalUserId في Flutter)

        $playerIds = method_exists($this->notifiable, 'getActiveOneSignalPlayerIds')
            ? $this->notifiable->getActiveOneSignalPlayerIds()
            : [];

        $data = array_merge($this->payload, ['type' => $this->type]);

        if (!empty($playerIds)) {
            // إرسال مباشر عبر Player IDs (الأكثر دقة)
            $success = $oneSignalService->sendToPlayerIds($playerIds, $this->title, $this->body, $data);
        } else {
            // Fallback: إرسال عبر external_id = patient->id
            // (يعمل إذا كان التطبيق استدعى OneSignal.login(patientId) عند الدخول)
            $externalId = method_exists($this->notifiable, 'routeNotificationForOneSignal')
                ? $this->notifiable->routeNotificationForOneSignal()
                : (string) $this->notifiable->id;

            $success = $oneSignalService->sendToUser($externalId, $this->title, $this->body, $data);
        }

        NotificationLog::create([
            'notification_type' => 'App\Jobs\SendPushNotificationJob',
            'notifiable_type'   => get_class($this->notifiable),
            'notifiable_id'     => $this->notifiable->id,
            'status'            => $success ? 'sent' : 'failed',
            'response'          => [],
            'error'             => $success ? null : 'Failed to send via OneSignalService',
            'sent_at'           => now(),
        ]);
    }
}
