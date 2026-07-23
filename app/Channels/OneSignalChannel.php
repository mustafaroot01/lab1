<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\Services\Notifications\OneSignalService;
use App\Models\NotificationLog;

class OneSignalChannel
{
    public function __construct(
        protected OneSignalService $oneSignalService
    ) {}

    public function send($notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toOneSignal')) {
            return;
        }

        // Get OneSignalMessageDTO
        $dto = $notification->toOneSignal($notifiable);

        // Add external ID if missing
        if (empty($dto->externalIds)) {
            if (method_exists($notifiable, 'routeNotificationForOneSignal')) {
                $dto->externalIds = [$notifiable->routeNotificationForOneSignal()];
            } else {
                $dto->externalIds = [(string) $notifiable->id];
            }
        }

        // Send via service
        $success = $this->oneSignalService->send($dto);

        // Log the result
        NotificationLog::create([
            'notification_type' => get_class($notification),
            'notifiable_type'   => get_class($notifiable),
            'notifiable_id'     => $notifiable->id,
            'status'            => $success ? 'sent' : 'failed',
            'response'          => [],
            'error'             => $success ? null : 'OneSignal API failed to send',
            'sent_at'           => now(),
        ]);
    }
}
