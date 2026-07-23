<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Chat\ChatService;
use App\Http\Requests\Chat\GetMessagesRequest;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Requests\Chat\UploadAttachmentRequest;
use App\Http\Requests\Chat\MarkAsReadRequest;
use App\Http\Resources\Chat\ConversationResource;
use App\Http\Resources\Chat\MessageResource;

class ChatController extends Controller
{
    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Get the current user type and ID based on authentication
     */
    private function getCurrentUser()
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        // Determine user type (Patient vs Admin)
        $userType = ($user instanceof \App\Models\Admin || (method_exists($user, 'isAdmin') && $user->isAdmin())) ? 'Admin' : 'Patient';
        
        return [
            'id' => (string) $user->id,
            'type' => $userType
        ];
    }

    /**
     * GET /chat/conversations
     */
    public function getConversations()
    {
        $user = $this->getCurrentUser();
        $conversations = $this->chatService->getUserConversations($user['id'], $user['type']);

        return response()->json([
            'status' => true,
            'message' => 'تم جلب المحادثات بنجاح',
            'data' => ConversationResource::collection($conversations)
        ]);
    }

    /**
     * GET /chat/patient/{patientId}/history
     */
    public function getPatientHistory(string $patientId)
    {
        $user = $this->getCurrentUser();
        
        // Ensure only Admin can view other people's history (unless you want to add permissions)
        if ($user['type'] !== 'Admin') {
            return response()->json(['status' => false, 'message' => 'Not authorized'], 403);
        }

        $conversations = $this->chatService->getPatientHistory($patientId);

        return response()->json([
            'status' => true,
            'message' => 'تم جلب تاريخ المحادثات للمريض بنجاح',
            'data' => ConversationResource::collection($conversations)
        ]);
    }

    /**
     * GET /chat/messages?conversation_id=xxx&before=xxx&limit=30
     */
    public function getMessages(GetMessagesRequest $request)
    {
        $messages = $this->chatService->getMessages(
            $request->validated('conversation_id'),
            $request->validated('before')
        );

        return response()->json([
            'status' => true,
            'message' => 'تم جلب الرسائل بنجاح',
            'data' => MessageResource::collection($messages)
        ]);
    }

    /**
     * POST /chat/messages
     */
    public function sendMessage(SendMessageRequest $request)
    {
        $user = $this->getCurrentUser();

        $message = $this->chatService->sendTextMessage(
            $request->validated('conversation_id'),
            $user['type'],
            $user['id'],
            $request->validated('text'),
            $request->validated('client_message_id')
        );

        return response()->json([
            'status' => true,
            'message' => 'تم إرسال الرسالة بنجاح',
            'data' => new MessageResource($message)
        ]);
    }

    /**
     * POST /chat/upload
     */
    public function uploadAttachment(UploadAttachmentRequest $request)
    {
        $user = $this->getCurrentUser();

        $message = $this->chatService->sendImageMessage(
            $request->validated('conversation_id'),
            $user['type'],
            $user['id'],
            $request->file('attachment'),
            $request->validated('client_message_id')
        );

        return response()->json([
            'status' => true,
            'message' => 'تم رفع الصورة وإرسالها بنجاح',
            'data' => new MessageResource($message)
        ]);
    }

    /**
     * POST /chat/read
     */
    public function markAsRead(MarkAsReadRequest $request)
    {
        $user = $this->getCurrentUser();

        $success = $this->chatService->markAsRead(
            $request->validated('conversation_id'),
            $user['type'],
            $user['id'],
            $request->validated('last_read_message_id')
        );

        return response()->json([
            'status' => $success,
            'message' => $success ? 'تم التحديث كمقروء' : 'فشل التحديث'
        ]);
    }

    /**
     * POST /chat/conversations/{id}/close
     */
    public function closeConversation(string $id)
    {
        // Only Admin usually closes chat, but we can verify auth
        $user = $this->getCurrentUser();
        if ($user['type'] !== 'Admin') {
            return response()->json(['status' => false, 'message' => 'Not authorized'], 403);
        }

        $success = $this->chatService->closeConversation($id);

        return response()->json([
            'status' => $success,
            'message' => $success ? 'تم إغلاق الدردشة بنجاح' : 'فشل في إغلاق الدردشة'
        ]);
    }
}
