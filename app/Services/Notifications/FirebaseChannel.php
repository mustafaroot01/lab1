<?php

namespace App\Services\Notifications;

use App\Models\DeviceToken;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseChannel
{
    /**
     * Send Push Notification via Firebase
     * 
     * @param array $tokens
     * @param string $title
     * @param string $body
     * @param array $payload
     * @return bool
     */
    public function send(array $tokens, string $title, string $body, array $payload = []): bool
    {
        try {
            $messaging = app('firebase.messaging');
            
            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData($payload);

            $report = $messaging->sendMulticast($message, $tokens);

            // Clean up invalid tokens
            if ($report->hasFailures()) {
                $invalidTokens = [];
                foreach ($report->failures()->getItems() as $failure) {
                    $invalidTokens[] = $failure->target()->value();
                }

                if (!empty($invalidTokens)) {
                    // Delete invalid tokens to keep the DB clean and avoid future errors
                    DeviceToken::whereIn('fcm_token', $invalidTokens)->delete();
                }
            }

            return $report->successes()->count() > 0;
            
        } catch (\Exception $e) {
            Log::error('Firebase Notification Error: ' . $e->getMessage());
            return false;
        }
    }
}
