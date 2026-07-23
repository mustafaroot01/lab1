<?php

namespace App\Repositories\Chat;

use App\Services\Chat\SupabaseClient;

class ChatRepository
{
    private SupabaseClient $supabase;

    public function __construct(SupabaseClient $supabase)
    {
        $this->supabase = $supabase;
    }

    /**
     * Create a new conversation
     */
    public function createConversation(array $data)
    {
        $response = $this->supabase->post('conversations', $data);
        if (!$response->successful()) {
            throw new \Exception('Failed to create conversation: ' . $response->body());
        }
        return $response->json()[0] ?? null;
    }

    /**
     * Add participants to a conversation
     */
    public function addParticipants(array $participants)
    {
        $response = $this->supabase->post('conversation_participants', $participants);
        if (!$response->successful()) {
            throw new \Exception('Failed to add participants: ' . $response->body());
        }
        return $response->json();
    }

    /**
     * Get conversations for a specific user
     */
    public function getUserConversations(string $userId, string $userType)
    {
        // First get participant records for this user
        $participantsResponse = $this->supabase->get('conversation_participants', [
            'user_id' => "eq.{$userId}",
            'user_type' => "eq.{$userType}",
            'select' => 'conversation_id'
        ]);

        if (!$participantsResponse->successful()) {
            throw new \Exception('Failed to fetch user conversations: ' . $participantsResponse->body());
        }

        $conversationIds = collect($participantsResponse->json())->pluck('conversation_id')->toArray();

        if (empty($conversationIds)) {
            return [];
        }

        // Fetch the actual conversations
        // Supabase IN operator syntax: id=in.(id1,id2)
        $inQuery = implode(',', $conversationIds);
        $conversationsResponse = $this->supabase->get('conversations', [
            'id' => "in.({$inQuery})",
            'order' => 'last_message_at.desc'
        ]);

        return $conversationsResponse->successful() ? $conversationsResponse->json() : [];
    }

    /**
     * Get history of conversations for a specific patient (Admin only typically)
     */
    public function getPatientConversations(string $patientId)
    {
        return $this->getUserConversations($patientId, 'Patient');
    }

    /**
     * Get messages for a conversation (Cursor Pagination)
     */
    public function getMessages(string $conversationId, ?string $beforeMessageId = null, int $limit = 30)
    {
        $query = [
            'conversation_id' => "eq.{$conversationId}",
            'order' => 'created_at.desc',
            'limit' => $limit
        ];

        // We can't use `beforeMessageId` easily with PostgREST without knowing its created_at.
        // Usually, the frontend sends the timestamp or we query the message first.
        // For simplicity, we can expect `before_created_at` timestamp instead of message_id.
        
        $response = $this->supabase->get('messages', $query);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch messages: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Create a message
     */
    public function createMessage(array $data)
    {
        $response = $this->supabase->post('messages', $data);
        if (!$response->successful()) {
            throw new \Exception('Failed to create message: ' . $response->body());
        }

        return $response->json()[0] ?? null;
    }

    /**
     * Update conversation last message
     */
    public function updateConversationLastMessage(string $conversationId, string $text, string $senderId)
    {
        // PostgREST PATCH requires query params for the WHERE clause
        $response = $this->supabase->patch('conversations', [
            'last_message' => $text,
            'last_sender_id' => $senderId,
            'last_message_at' => now()->toIso8601String()
        ], ['id' => "eq.{$conversationId}"]);

        if (!$response->successful()) {
            throw new \Exception('Failed to update conversation: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Update conversation status (Close chat)
     */
    public function updateConversationStatus(string $conversationId, string $status)
    {
        $response = $this->supabase->patch('conversations', [
            'status' => $status
        ], ['id' => "eq.{$conversationId}"]);

        return $response->successful();
    }

    /**
     * Update participants last read message
     */
    public function updateParticipantLastRead(string $conversationId, string $userType, string $userId, string $messageId)
    {
        // Note: For PostgREST PATCH with multiple conditions, we pass them as query parameters
        $urlParams = [
            'conversation_id' => "eq.{$conversationId}",
            'user_type' => "eq.{$userType}",
            'user_id' => "eq.{$userId}"
        ];

        $response = $this->supabase->patch('conversation_participants', [
            'last_read_message_id' => $messageId,
            'last_read_at' => now()->toIso8601String()
        ], $urlParams);

        return $response->successful();
    }
}
