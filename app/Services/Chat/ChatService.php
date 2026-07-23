<?php

namespace App\Services\Chat;

use App\Repositories\Chat\ChatRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatService
{
    private ChatRepository $repository;

    public function __construct(ChatRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Fetch conversations for user
     */
    public function getUserConversations(string $userId, string $userType)
    {
        return $this->repository->getUserConversations($userId, $userType);
    }

    /**
     * Fetch conversations for a specific patient
     */
    public function getPatientHistory(string $patientId)
    {
        return $this->repository->getPatientConversations($patientId);
    }

    /**
     * Fetch messages (with Cursor limit)
     */
    public function getMessages(string $conversationId, ?string $beforeTimestamp = null)
    {
        // For cursor, we can pass it to repository. Currently using simple fetch
        return $this->repository->getMessages($conversationId, $beforeTimestamp);
    }

    /**
     * Create a text message
     */
    public function sendTextMessage(string $conversationId, string $senderType, string $senderId, string $text, string $clientMessageId)
    {
        $payload = [
            'client_message_id' => $clientMessageId,
            'conversation_id' => $conversationId,
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'message_type' => 'TEXT',
            'text' => $text,
            'status' => 'SENT',
        ];

        // 1. Create message in Supabase
        $message = $this->repository->createMessage($payload);

        // 2. Update conversation
        $this->repository->updateConversationLastMessage($conversationId, $text, $senderId);

        // 3. Send Notification to the other party
        $this->sendNotification($conversationId, $senderType, $text);

        return $message;
    }

    /**
     * Upload attachment and create an image message
     */
    public function sendImageMessage(string $conversationId, string $senderType, string $senderId, $file, string $clientMessageId)
    {
        // Upload file to local storage (or S3)
        $path = $file->store('chats', 'public');
        $url = asset('storage/' . $path);

        // Optional: Extract metadata (width, height) using getimagesize if possible
        $metadata = [];
        try {
            $sizes = getimagesize($file->getPathname());
            if ($sizes) {
                $metadata = [
                    'width' => $sizes[0],
                    'height' => $sizes[1],
                    'size' => $file->getSize()
                ];
            }
        } catch (\Exception $e) {
            // Ignore if not an image or fails
        }

        $payload = [
            'client_message_id' => $clientMessageId,
            'conversation_id' => $conversationId,
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'message_type' => 'IMAGE',
            'text' => '📷 صورة',
            'attachment_url' => $url,
            'metadata' => $metadata,
            'status' => 'SENT',
        ];

        $message = $this->repository->createMessage($payload);

        $this->repository->updateConversationLastMessage($conversationId, '📷 صورة', $senderId);
        $this->sendNotification($conversationId, $senderType, '📷 صورة');

        return $message;
    }

    /**
     * Mark conversation as read
     */
    public function markAsRead(string $conversationId, string $userType, string $userId, string $lastReadMessageId)
    {
        return $this->repository->updateParticipantLastRead($conversationId, $userType, $userId, $lastReadMessageId);
    }

    /**
     * Close the chat
     */
    public function closeConversation(string $conversationId)
    {
        return $this->repository->updateConversationStatus($conversationId, 'CLOSED');
    }

    /**
     * Send Push Notification
     */
    private function sendNotification(string $conversationId, string $senderType, string $text)
    {
        // Simple OneSignal integration
        $appId = config('services.onesignal.app_id');
        $apiKey = config('services.onesignal.rest_api_key');

        if (empty($appId) || empty($apiKey)) {
            return;
        }

        // Normally we need to find who is the receiver in the `conversation_participants`
        // We'll leave this generic for now.
        // We'll target the app via segments or specific user external_id if we have it
        try {
            Http::withHeaders([
                'Authorization' => 'Basic ' . $apiKey,
                'Content-Type' => 'application/json'
            ])->post(config('services.onesignal.api_url', 'https://onesignal.com/api/v1/notifications'), [
                'app_id' => $appId,
                'headings' => ['en' => 'رسالة جديدة', 'ar' => 'رسالة جديدة'],
                'contents' => ['en' => $text, 'ar' => $text],
                'included_segments' => ['All'] // In production, replace with include_external_user_ids
            ]);
        } catch (\Exception $e) {
            // Log error
        }
    }
}
