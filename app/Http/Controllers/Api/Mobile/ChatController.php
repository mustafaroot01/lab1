<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Services\Chat\ChatService;
use App\Http\Requests\Chat\GetMessagesRequest;
use App\Http\Requests\Chat\SendMessageRequest;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    private function getPatientId()
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthorized');
        }
        return (string) $user->id;
    }

    /**
     * بدء أو جلب المحادثة النشطة (المريض لا يرى الأرشيف)
     */
    public function init()
    {
        $patientId = $this->getPatientId();
        
        // جلب المحادثات الحالية للمريض
        $conversations = $this->chatService->getUserConversations($patientId, 'Patient');
        
        // البحث عن محادثة مفتوحة
        $activeConversation = collect($conversations)->first(function ($conv) {
            return ($conv['conversation']['status'] ?? '') === 'OPEN';
        });

        if ($activeConversation) {
            return response()->json([
                'status' => true,
                'conversation' => $activeConversation
            ]);
        }

        // إذا لم توجد محادثة مفتوحة، نكتفي بإرسال null، ليقوم الموبايل بعرض شاشة فارغة
        // وبمجرد إرسال أول رسالة، سيتم تكوينها (حسب لوجيك الإرسال)
        return response()->json([
            'status' => true,
            'conversation' => null
        ]);
    }

    /**
     * جلب رسائل محادثة معينة
     */
    public function getMessages(Request $request, string $id)
    {
        $beforeTimestamp = $request->query('before');
        $messages = $this->chatService->getMessages($id, $beforeTimestamp);

        return response()->json([
            'status' => true,
            'messages' => $messages,
        ]);
    }

    /**
     * إرسال رسالة (نصية أو صورة)
     */
    public function sendMessage(SendMessageRequest $request, string $id)
    {
        $patientId = $this->getPatientId();
        $clientMessageId = $request->input('client_message_id', uniqid('client_'));

        if ($request->hasFile('file')) {
            $message = $this->chatService->sendImageMessage($id, 'Patient', $patientId, $request->file('file'), $clientMessageId);
        } else {
            $message = $this->chatService->sendTextMessage($id, 'Patient', $patientId, $request->input('text'), $clientMessageId);
        }

        return response()->json([
            'status' => true,
            'message' => $message,
        ]);
    }
}
