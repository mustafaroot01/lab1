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
     * Get all conversations (For Admin Dashboard)
     */
    public function getAllConversations(array $filters = [])
    {
        $query = ['order' => 'last_message_at.desc'];
        
        if (!empty($filters['status'])) {
            $query['status'] = "eq.{$filters['status']}";
        }

        $response = $this->supabase->get('conversations', $query);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch all conversations: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Get conversation by ID
     */
    public function getConversationById(string $id)
    {
        $response = $this->supabase->get('conversations', [
            'id' => "eq.{$id}",
            'limit' => 1
        ]);

        if (!$response->successful() || empty($response->json())) {
            return null;
        }

        return $response->json()[0];
    }

    /**
     * Get conversations for a specific user
     */
    public function getUserConversations(string $userId, string $userType)
    {
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

        $inQuery = implode(',', $conversationIds);
        $conversationsResponse = $this->supabase->get('conversations', [
            'id' => "in.({$inQuery})",
            'order' => 'last_message_at.desc'
        ]);

        return $conversationsResponse->successful() ? $conversationsResponse->json() : [];
    }

    /**
     * Get history of conversations for a specific patient
     */
    public function getPatientConversations(string $patientId)
    {
        return $this->getUserConversations($patientId, 'Patient');
    }

    /**
     * Get messages for a conversation
     */
    public function getMessages(string $conversationId, ?string $beforeMessageId = null, int $limit = 30)
    {
        $query = [
            'conversation_id' => "eq.{$conversationId}",
            'order' => 'created_at.desc',
            'limit' => $limit
        ];
        
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
     * Update conversation status (Close/Reopen chat)
     */
    public function updateConversationStatus(string $conversationId, string $status)
    {
        $response = $this->supabase->patch('conversations', [
            'status' => $status
        ], ['id' => "eq.{$conversationId}"]);

        return $response->successful();
    }

    /**
     * Claim conversation (assign to an admin)
     */
    public function claimConversation(string $conversationId, string $adminId)
    {
        $response = $this->supabase->patch('conversations', [
            'assigned_to' => $adminId,
            'is_assigned' => true,
            'assigned_at' => now()->toIso8601String(),
            'claimed_by' => $adminId,
            'claimed_at' => now()->toIso8601String()
        ], ['id' => "eq.{$conversationId}"]);

        return $response->successful();
    }

    /**
     * Update participants last read message
     */
    public function updateParticipantLastRead(string $conversationId, string $userType, string $userId, string $messageId)
    {
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
