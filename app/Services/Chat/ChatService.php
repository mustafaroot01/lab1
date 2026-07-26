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
    public function openConversation(string $conversationId)
    {
        $conversation = $this->chatRepository->getConversationById($conversationId);
        if (!$conversation) {
            return null;
        }

        $messages = $this->getMessages($conversationId);

        $patientHistory = [];
        if (!empty($conversation['patient_id'])) {
            $patientHistory = $this->getPatientHistory($conversation['patient_id']);
        }

        return $this->assembler->assembleFullView($conversation, $messages, $patientHistory);
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
     * Start a new conversation for a patient
     */
    public function startPatientConversation(string $patientId)
    {
        $conversation = $this->chatRepository->createConversation([
            'patient_id' => $patientId,
            'status' => 'OPEN',
            'assigned_to' => null
        ]);

        $this->chatRepository->addParticipants([
            [
                'conversation_id' => $conversation['id'],
                'user_type' => 'Patient',
                'user_id' => $patientId
            ]
        ]);

        return $this->assembler->assembleFullView($conversation, [], []);
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
     * Fetch messages - returns in ascending order (oldest first) for the chat UI
     */
    public function getMessages(string $conversationId, ?string $beforeTimestamp = null)
    {
        // Fetch from Supabase in DESC order (newest first) then reverse to get ASC (oldest first)
        $messages = $this->chatRepository->getMessages($conversationId, $beforeTimestamp, 30);
        $mapped = array_map(function ($msg) {
            return $this->assembler->assembleMessage($msg);
        }, $messages);

        return array_values(array_reverse($mapped));
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

        return $this->assembler->assembleMessage($message);
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
        } catch (\Exception $e) {}

        $payload = [
            'client_message_id' => $clientMessageId,
            'conversation_id' => $conversationId,
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'message_type' => 'IMAGE',
            'text' => 'صورة مرفقة',
            'attachment_url' => $url,
            'attachment_metadata' => $metadata,
            'status' => 'SENT',
        ];

        $message = $this->chatRepository->createMessage($payload);
        $this->chatRepository->updateConversationLastMessage($conversationId, 'صورة مرفقة', $senderId);
        $this->sendNotification($conversationId, $senderType, 'صورة مرفقة');

        return $this->assembler->assembleMessage($message);
    }

    /**
     * Mark as read
     */
    public function markAsRead(string $conversationId, string $userType, string $userId, string $lastReadMessageId)
    {
        return $this->chatRepository->updateParticipantLastRead($conversationId, $userType, $userId, $lastReadMessageId);
    }

    /**
     * Close the chat - returns the updated conversation data
     */
    public function closeConversation(string $conversationId): array
    {
        $this->chatRepository->updateConversationStatus($conversationId, 'CLOSED');
        $closedAt = now()->toIso8601String();
        return ['closed_at' => $closedAt, 'status' => 'closed'];
    }

    /**
     * Reopen the chat - returns the updated conversation data
     */
    public function reopenConversation(string $conversationId): array
    {
        $this->chatRepository->updateConversationStatus($conversationId, 'OPEN');
        return ['closed_at' => null, 'status' => 'open'];
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

        // Only send notification if Admin replies (send to patient)
        if ($senderType !== 'Admin') {
            return;
        }

        try {
            $conversation = $this->chatRepository->getConversationById($conversationId);
            if (!$conversation || empty($conversation['patient_id'])) {
                return;
            }

            $patientId = (string) $conversation['patient_id'];

            Http::withHeaders([
                'Authorization' => 'Basic ' . $apiKey,
                'Content-Type' => 'application/json'
            ])->post(config('services.onesignal.api_url', 'https://onesignal.com/api/v1/notifications'), [
                'app_id' => $appId,
                'headings' => ['en' => 'رسالة جديدة من الإدارة', 'ar' => 'رسالة جديدة من الإدارة'],
                'contents' => ['en' => $text, 'ar' => $text],
                'include_external_user_ids' => [$patientId]
            ]);
        } catch (\Exception $e) {
            // Log error silently — notifications are non-critical
        }
    }
}
