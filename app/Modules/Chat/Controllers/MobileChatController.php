<?php

namespace App\Modules\Chat\Controllers;

use App\Enums\Chat\ConversationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Resources\Chat\MessageResource;
use App\Models\Chat\Conversation;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;

class MobileChatController extends Controller
{
    public function __construct(private ChatService $chatService)
    {
    }

    /**
     * جلب أو إنشاء محادثة المريض — مع تحديد الحالة الافتراضية صراحة
     */
    private function resolveConversation(int $userId): Conversation
    {
        $conversation = Conversation::where(function($q) use ($userId) {
            $q->where('patient_id', $userId)->orWhere('user_id', $userId);
        })
        ->where('status', ConversationStatus::Open->value)
        ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'patient_id' => $userId,
                'user_id'    => $userId,
                'status'     => ConversationStatus::Open->value,
            ]);
        }

        return $conversation;
    }

    /**
     * جلب محادثة المريض الحالية + الرسائل (cursor pagination)
     */
    public function show(Request $request)
    {
        $user = $request->user();

        $conversation = $this->resolveConversation($user->id);

        $perPage = min((int) $request->input('per_page', 30), 100);
        $messages = $conversation->messages()
            ->with(['senderAdmin:id,name,role', 'senderPatient:id,name'])
            ->orderByDesc('id')
            ->cursorPaginate($perPage);

        $nextCursor = $messages->nextCursor()?->encode();
        $hasMore = $messages->hasMorePages();

        $messages->getCollection()->each(fn ($m) => $m->setRelation('conversation', $conversation));
        $messages->setCollection($messages->getCollection()->reverse()->values());

        $this->chatService->markReadByPatient($conversation);

        return response()->json([
            'status'       => true,
            'conversation' => [
                'id'     => $conversation->id,
                'status' => $conversation->status->value,
            ],
            'messages' => MessageResource::collection($messages->items()),
            'meta'     => [
                'next_cursor' => $nextCursor,
                'has_more'    => $hasMore,
                'per_page'    => $perPage,
            ],
        ]);
    }

    /**
     * تحميل رسائل أقدم (load more) — cursor pagination
     */
    public function loadMoreMessages(Request $request)
    {
        $request->validate(['cursor' => 'required|string']);

        $user = $request->user();
        $conversation = $this->resolveConversation($user->id);

        $perPage = min((int) $request->input('per_page', 30), 100);

        $messages = $conversation->messages()
            ->with(['senderAdmin:id,name,role', 'senderPatient:id,name'])
            ->orderByDesc('id')
            ->cursorPaginate($perPage, ['*'], 'cursor', \Illuminate\Pagination\Cursor::fromEncoded($request->cursor));

        $nextCursor = $messages->nextCursor()?->encode();
        $hasMore = $messages->hasMorePages();

        $messages->getCollection()->each(fn ($m) => $m->setRelation('conversation', $conversation));
        $messages->setCollection($messages->getCollection()->reverse()->values());

        return response()->json([
            'status'   => true,
            'messages' => MessageResource::collection($messages->items()),
            'meta'     => [
                'next_cursor' => $nextCursor,
                'has_more'    => $hasMore,
            ],
        ]);
    }

    /**
     * إرسال رسالة من المريض
     */
    public function send(SendMessageRequest $request, ChatService $chatService)
    {
        $user = $request->user();
        $conversation = $this->resolveConversation($user->id);

        $message = $chatService->sendMessage(
            $conversation,
            $user,
            $request->input('body'),
            $request->file('attachment'),
        );

        return response()->json([
            'status'  => true,
            'message' => 'تم إرسال الرسالة',
            'data'    => new MessageResource($message->load(['senderAdmin:id,name,role', 'senderPatient:id,name'])),
        ], 201);
    }

    /**
     * تعليم المحادثة كمقروءة للمريض
     */
    public function markRead(Request $request, ChatService $chatService)
    {
        $user = $request->user();
        $conversation = $this->resolveConversation($user->id);
        $chatService->markReadByPatient($conversation);

        return response()->json([
            'status'  => true,
            'message' => 'تم تعليم المحادثة كمقروءة',
        ]);
    }

    /**
     * جلب قائمة المحادثات السابقة (المغلقة) للمريض للاطلاع عليها كمرجع
     */
    public function history(Request $request)
    {
        $userId = $request->user()->id;
        $conversations = Conversation::where(function($q) use ($userId) {
                $q->where('patient_id', $userId)->orWhere('user_id', $userId);
            })
            ->where('status', ConversationStatus::Closed->value)
            ->latest('closed_at')
            ->get();

        return response()->json([
            'status' => true,
            'conversations' => \App\Http\Resources\Chat\ConversationResource::collection($conversations),
        ]);
    }
}
