<?php

namespace App\Services\Notifications;

use App\Models\AppNotification;
use App\Models\DeviceToken;

class NotificationDispatcher
{
    /**
     * Dispatch the notification to all available channels
     */
    public static function dispatch($notifiable, string $title, string $body, string $type, array $payload = [])
    {
        // 1. Save to database for in-app history
        $appNotification = AppNotification::create([
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'payload' => $payload,
            'status' => 'pending',
        ]);

        // 2. Fetch all active device tokens for this user
        $tokens = DeviceToken::where('tokenable_type', get_class($notifiable))
            ->where('tokenable_id', $notifiable->id)
            ->where('is_active', true)
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            $appNotification->update(['status' => 'no_devices_found']);
            return;
        }

        // 3. Send via Firebase
        $firebaseChannel = new FirebaseChannel();
        $success = $firebaseChannel->send($tokens, $title, $body, $payload);

        // 4. Update status
        $appNotification->update([
            'status' => $success ? 'sent' : 'failed',
            'sent_at' => $success ? now() : null,
        ]);
    }
}
