<?php

namespace App\Listeners;

use App\Events\Chat\MessageCreated;
use App\Models\Chat\Message;
use App\Notifications\NewChatMessageNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendChatNotificationListener
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\Chat\MessageCreated  $event
     * @return void
     */
    public function handle(MessageCreated $event)
    {
        $message = $event->message;

        // Only send push notification if the sender is an admin
        if ($message->sender_type === Message::SENDER_ADMIN) {
            // Load conversation if not loaded
            $conversation = $message->conversation;
            
            if ($conversation && $conversation->patient) {
                $patient = $conversation->patient;

                // Send notification only if they have an FCM token
                if ($patient->fcm_token) {
                    $patient->notify(new NewChatMessageNotification($message));
                }
            }
        }
    }
}
