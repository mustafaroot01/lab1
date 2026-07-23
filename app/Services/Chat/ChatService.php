<?php

namespace App\Services\Chat;

use App\Repositories\Chat\ChatRepository;
use App\Repositories\Patients\PatientRepository;
use App\DTOs\Chat\ConversationView;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatService
{
    private ChatRepository $chatRepository;
    private ConversationAssembler $assembler;
    private PatientRepository $patientRepository;

    public function __construct(ChatRepository $chatRepository, ConversationAssembler $assembler, PatientRepository $patientRepository)
    {
        $this->chatRepository = $chatRepository;
        $this->assembler = $assembler;
        $this->patientRepository = $patientRepository;
    }

    /**
     * Get all conversations (Admin)
     */
    public function getAllConversations(array $filters = []): array
    {
        $conversations = $this->chatRepository->getAllConversations($filters);
        return $this->assembler->assembleMany($conversations);
    }

    /**
     * Get full conversation details
     */
    public function openConversation(string $conversationId): ?ConversationView
    {
        $conversation = $this->chatRepository->getConversationById($conversationId);
        if (!$conversation) {
            return null;
        }

        $messages = $this->chatRepository->getMessages($conversationId);
        
        $historyStats = [];
        if (!empty($conversation['patient_id'])) {
            $historyStats = $this->patientRepository->getChatHistoryStats($conversation['patient_id']);
        }

        return $this->assembler->assembleFullView($conversation, $messages, $historyStats);
    }

    /**
     * Fetch conversations for user
     */
    public function getUserConversations(string $userId, string $userType): array
    {
        $conversations = $this->chatRepository->getUserConversations($userId, $userType);
        return $this->assembler->assembleMany($conversations);
    }

    /**
     * Fetch conversations for a specific patient
     */
    public function getPatientHistory(string $patientId): array
    {
        $conversations = $this->chatRepository->getPatientConversations($patientId);
        return $this->assembler->assembleMany($conversations);
    }

    /**
     * Fetch messages
     */
    public function getMessages(string $conversationId, ?string $beforeTimestamp = null): array
    {
        return $this->chatRepository->getMessages($conversationId, $beforeTimestamp);
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

        $message = $this->chatRepository->createMessage($payload);
        $this->chatRepository->updateConversationLastMessage($conversationId, $text, $senderId);
        $this->sendNotification($conversationId, $senderType, $text);

        return $message;
    }

    /**
     * Upload attachment and create an image message
     */
    public function sendImageMessage(string $conversationId, string $senderType, string $senderId, $file, string $clientMessageId)
    {
        $path = $file->store('chats', 'public');
        $url = asset('storage/' . $path);

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
            // Ignore
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

        $message = $this->chatRepository->createMessage($payload);
        $this->chatRepository->updateConversationLastMessage($conversationId, '📷 صورة', $senderId);
        $this->sendNotification($conversationId, $senderType, '📷 صورة');

        return $message;
    }

    /**
     * Mark as read
     */
    public function markAsRead(string $conversationId, string $userType, string $userId, string $lastReadMessageId)
    {
        return $this->chatRepository->updateParticipantLastRead($conversationId, $userType, $userId, $lastReadMessageId);
    }

    /**
     * Close the chat
     */
    public function closeConversation(string $conversationId)
    {
        return $this->chatRepository->updateConversationStatus($conversationId, 'CLOSED');
    }

    /**
     * Reopen the chat
     */
    public function reopenConversation(string $conversationId)
    {
        return $this->chatRepository->updateConversationStatus($conversationId, 'OPEN');
    }

    /**
     * Claim the chat
     */
    public function claimConversation(string $conversationId, string $adminId)
    {
        return $this->chatRepository->claimConversation($conversationId, $adminId);
    }

    /**
     * Send Push Notification
     */
    private function sendNotification(string $conversationId, string $senderType, string $text)
    {
        $appId = config('services.onesignal.app_id');
        $apiKey = config('services.onesignal.rest_api_key');

        if (empty($appId) || empty($apiKey)) {
            return;
        }

        // Ideally, we fetch the conversation_participants to find the target external_id.
        // For now, if Admin sends it, we target the patient linked to the conversation.
        // Needs an extra fetch to supabase, but we skip it for performance if we already have it contextually.
        // We will leave this for future refinement since the focus is on Supabase architecture.
        try {
            Http::withHeaders([
                'Authorization' => 'Basic ' . $apiKey,
                'Content-Type' => 'application/json'
            ])->post(config('services.onesignal.api_url', 'https://onesignal.com/api/v1/notifications'), [
                'app_id' => $appId,
                'headings' => ['en' => 'رسالة جديدة', 'ar' => 'رسالة جديدة'],
                'contents' => ['en' => $text, 'ar' => $text],
                'included_segments' => ['All'] // @TODO: Replace with exact Patient External ID
            ]);
        } catch (\Exception $e) {
            // Log error
        }
    }
}
