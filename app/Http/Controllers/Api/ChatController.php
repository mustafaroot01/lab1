<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Chat\ChatService;
use App\Http\Requests\Chat\GetMessagesRequest;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Requests\Chat\UploadAttachmentRequest;
use App\Http\Requests\Chat\MarkAsReadRequest;
use Illuminate\Http\Request;
use App\Models\Chat\CannedResponse;

class ChatController extends Controller
{
    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    protected function getCurrentUser(): array
    {
        $user = auth()->user();
        
        // This controller is protected by 'role:admin' middleware, so all users here are Admins.
        return [
            'id' => (string) $user->id,
            'type' => 'Admin'
        ];
    }

    /**
     * GET /admin/chat
     */
    public function getConversations(Request $request)
    {
        $filters = $request->only(['status', 'assigned_status']);
        $conversations = $this->chatService->getAllConversations($filters);

        return response()->json([
            'status' => true,
            'conversations' => $conversations,
            'meta' => ['has_more' => false] // To be implemented with pagination if needed
        ]);
    }

    /**
     * GET /admin/chat/{id}
     */
    public function openConversation(string $id)
    {
        $conversationView = $this->chatService->openConversation($id);
        
        if (!$conversationView) {
            return response()->json(['status' => false, 'message' => 'المحادثة غير موجودة'], 404);
        }

        return response()->json(array_merge(['status' => true], $conversationView));
    }

    /**
     * GET /admin/chat/patient/{patientId}/history
     */
    public function getPatientHistory(string $patientId)
    {
        $history = $this->chatService->getPatientHistory($patientId);

        return response()->json([
            'status' => true,
            'history' => $history,
            'meta' => ['has_more' => false, 'total_count' => count($history)]
        ]);
    }

    /**
     * GET /admin/chat/patient/{patientId}/profile
     */
    public function getPatientProfile(string $patientId)
    {
        // Simple dummy endpoint to satisfy Vue frontend requirements
        return response()->json([
            'status' => true,
            'profile' => [
                'id' => $patientId,
                'name' => 'المريض',
                'phone' => '-',
                'about' => 'مريض مسجل بالنظام',
                'status' => 'online'
            ]
        ]);
    }

    /**
     * GET /admin/chat/{id}/messages
     */
    public function getMessages(string $id, Request $request)
    {
        $messages = $this->chatService->getMessages($id, $request->query('cursor'));

        return response()->json([
            'status' => true,
            'messages' => $messages,
            'meta' => ['has_more' => false]
        ]);
    }

    /**
     * POST /admin/chat/{id}/send
     */
    public function sendMessage(string $id, Request $request)
    {
        $user = $this->getCurrentUser();
        
        // Use standard validation for rapid hybrid integration
        $request->validate([
            'body' => 'nullable|string',
            'attachment' => 'nullable|file',
            'client_message_id' => 'nullable|string'
        ]);

        $clientMessageId = $request->input('client_message_id', (string) \Illuminate\Support\Str::uuid());
        
        if ($request->hasFile('attachment')) {
            $message = $this->chatService->sendImageMessage(
                $id,
                $user['type'],
                $user['id'],
                $request->file('attachment'),
                $clientMessageId
            );
        } else {
            $message = $this->chatService->sendTextMessage(
                $id,
                $user['type'],
                $user['id'],
                $request->input('body', ''),
                $clientMessageId
            );
        }

        return response()->json([
            'status' => true,
            'data' => $message
        ]);
    }

    /**
     * POST /admin/chat/{id}/read
     */
    public function markAsRead(string $id, Request $request)
    {
        $user = $this->getCurrentUser();
        
        $lastReadMsgId = $request->input('last_read_message_id', 'latest'); // Dummy for now since frontend doesn't pass it perfectly

        $success = $this->chatService->markAsRead(
            $id,
            $user['type'],
            $user['id'],
            $lastReadMsgId
        );

        return response()->json(['status' => $success]);
    }

    /**
     * POST /admin/chat/{id}/close
     */
    public function closeConversation(string $id)
    {
        $data = $this->chatService->closeConversation($id);

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }

    /**
     * POST /admin/chat/{id}/reopen
     */
    public function reopenConversation(string $id)
    {
        $data = $this->chatService->reopenConversation($id);

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }

    /**
     * POST /admin/chat/{id}/claim
     */
    public function claimConversation(string $id)
    {
        $user = $this->getCurrentUser();
        
        $success = $this->chatService->claimConversation($id, $user['id']);

        return response()->json([
            'status' => $success,
            'data' => [
                'assigned_to' => $user['id'], // Typically would fetch the Admin name here
                'is_assigned' => true,
                'assigned_at' => now()->toIso8601String()
            ]
        ]);
    }

    /**
     * GET /admin/chat/canned-responses
     */
    public function getCannedResponses()
    {
        try {
            $responses = \App\Models\Chat\CannedResponse::where('is_active', true)->get(['id', 'title', 'content']);
        } catch (\Throwable $e) {
            // Fallback if table doesn't exist yet or other errors
            $responses = [];
        }

        return response()->json([
            'status' => true,
            'responses' => $responses
        ]);
    }
}
