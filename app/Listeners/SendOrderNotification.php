<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Jobs\SendPushNotificationJob;
use App\Services\Notifications\NotificationBuilder;

class SendOrderNotification
{
    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;
        $patient = $order->patient;

        if (!$patient) {
            return;
        }

        // Build the notification
        $messageData = NotificationBuilder::build($event->notificationType, $order);

        // Dispatch the job
        dispatch(new SendPushNotificationJob(
            $patient,
            $messageData['title'],
            $messageData['body'],
            $messageData['payload']['type'] ?? $event->notificationType->value,
            $messageData['payload'] ?? []
        ));
    }
}
