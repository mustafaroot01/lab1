<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Illuminate\Support\Facades\Log;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FirebaseChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $token = $notifiable->routeNotificationFor('firebase', $notification);

        if (!$token) {
            return;
        }

        if (!method_exists($notification, 'toFirebase')) {
            return;
        }

        $messageData = $notification->toFirebase($notifiable);

        if (empty($messageData)) {
            return;
        }

        $title = $messageData['title'] ?? '';
        $body = $messageData['body'] ?? '';
        $data = $messageData['data'] ?? [];

        $firebaseNotification = FirebaseNotification::create($title, $body);

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($firebaseNotification)
            ->withData($data);

        // collapse_key for Android to prevent notification spam
        $androidConfig = AndroidConfig::fromArray([
            'collapse_key' => $messageData['collapse_key'] ?? 'default',
        ]);
        $message = $message->withAndroidConfig($androidConfig);

        try {
            Firebase::messaging()->send($message);
            
            Log::info("FCM Notification sent successfully", [
                'notifiable_id' => $notifiable->id ?? null,
                'token_prefix'  => substr($token, 0, 10) . '...',
                'type'          => get_class($notification)
            ]);
        } catch (MessagingException $e) {
            Log::error("FCM Notification Failed", [
                'notifiable_id' => $notifiable->id ?? null,
                'token_prefix'  => substr($token, 0, 10) . '...',
                'error_msg'     => $e->getMessage(),
            ]);

            // Handle Unregistered / Expired / Invalid token
            $errorMsg = strtolower($e->getMessage());
            if (
                str_contains($errorMsg, 'unregistered') || 
                str_contains($errorMsg, 'invalid') ||
                str_contains($errorMsg, 'not found') ||
                str_contains($errorMsg, 'not_found')
            ) {
                // Clear the token from DB
                if (method_exists($notifiable, 'update')) {
                    $notifiable->update(['fcm_token' => null]);
                    Log::info("FCM Token cleared (unregistered/expired) for notifiable", [
                        'notifiable_id' => $notifiable->id ?? null
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("FCM General Error", [
                'notifiable_id' => $notifiable->id ?? null,
                'error'         => $e->getMessage()
            ]);
        }
    }
}
