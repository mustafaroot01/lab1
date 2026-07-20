<?php

namespace App\Modules\Chat\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Resources\Chat\ConversationResource;
use App\Http\Resources\Chat\MessageResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PatientResource;
use App\Models\Chat\Conversation;
use App\Models\Patient;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;

class AdminChatController extends Controller
{
    public function __construct(private ChatService $chatService)
    {
    }

    /**
     * قائمة المحادثات (لوحة التحكم) — مع فلترة وبحث + cursor pagination
     */
    public function index(Request $request)
    {
        $adminId = $request->user()->id;

        $query = Conversation::with([
            'patient:id,name,phone,district_id',
            'patient.district:id,name',
            'assignedTo:id,name',
            'closedBy:id,name',
        ])
            ->latest('last_message_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('assigned_status')) {
            if ($request->assigned_status === 'my_assigned') {
                $query->where('assigned_to_user_id', $adminId);
            } elseif ($request->assigned_status === 'unassigned') {
                $query->whereNull('assigned_to_user_id');
            }
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->whereHas('patient', function ($u) use ($q) {
                $u->where('name', 'like', "%$q%")
                  ->orWhere('phone', 'like', "%$q%");
            });
        }

        $perPage = min((int) $request->input('per_page', 20), 50);
        $conversations = $query->cursorPaginate($perPage);

        $conversations->getCollection()->loadCount([
            'messages as unread_count' => fn ($q) => $q
                ->where('sender_type', \App\Models\Chat\Message::SENDER_PATIENT)
                ->whereRaw('messages.id > COALESCE(conversations.admin_last_read_message_id, 0)'),
        ]);

        return response()->json([
            'status'        => true,
            'conversations' => ConversationResource::collection($conversations->items()),
            'meta'          => [
                'next_cursor'   => $conversations->nextCursor()?->encode(),
                'prev_cursor'   => $conversations->previousCursor()?->encode(),
                'has_more'      => $conversations->hasMorePages(),
                'per_page'      => $perPage,
            ],
        ]);
    }

    /**
     * عرض محادثة معينة + رسائلها (cursor pagination للرسائل)
     */
    public function show(Request $request, Conversation $conversation)
    {
        $conversation->load([
            'patient:id,name,phone,district_id',
            'patient.district:id,name',
            'assignedTo:id,name',
            'closedBy:id,name',
        ]);


        $perPage = min((int) $request->input('per_page', 30), 100);

        $messages = $conversation->messages()
            ->with(['senderAdmin:id,name,role', 'senderPatient:id,name'])
            ->orderByDesc('id')
            ->cursorPaginate($perPage);

        $nextCursor = $messages->nextCursor()?->encode();
        $hasMore = $messages->hasMorePages();

        $messages->getCollection()->each(fn ($m) => $m->setRelation('conversation', $conversation));
        $messages->setCollection($messages->getCollection()->reverse()->values());

        $this->chatService->markReadByAdmin($conversation);

        $patientId = $conversation->patient_id ?: $conversation->user_id;
        $historyQuery = Conversation::where(function($q) use ($patientId) {
                $q->where('patient_id', $patientId)->orWhere('user_id', $patientId);
            })
            ->where('id', '!=', $conversation->id);

        $patientHistoryTotalCount = (clone $historyQuery)->count();
        $patientHistory = (clone $historyQuery)
            ->latest('last_message_at')
            ->limit(5)
            ->get(['id', 'status', 'created_at', 'closed_at', 'last_message_preview']);

        return response()->json([
            'status'                     => true,
            'conversation'               => new ConversationResource($conversation),
            'messages'                   => MessageResource::collection($messages->items()),
            'patient_history'            => $patientHistory,
            'patient_history_total_count'=> $patientHistoryTotalCount,
            'meta'                       => [
                'next_cursor' => $nextCursor,
                'has_more'    => $hasMore,
                'per_page'    => $perPage,
            ],
        ]);
    }

    /**
     * تحميل رسائل أقدم (load more) — cursor pagination
     */
    public function loadMoreMessages(Request $request, Conversation $conversation)
    {
        $request->validate(['cursor' => 'required|string']);

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
     * إرسال رد من الأدمن
     */
    public function send(SendMessageRequest $request, Conversation $conversation, ChatService $chatService)
    {
        if (!$conversation->isOpen()) {
            return response()->json([
                'status'  => false,
                'message' => 'المحادثة مغلقة — لا يمكن إرسال رسائل',
            ], 422);
        }

        $message = $chatService->sendMessage(
            $conversation,
            $request->user(),
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
     * إغلاق المحادثة
     */
    public function close(Request $request, Conversation $conversation, ChatService $chatService)
    {
        $chatService->close($conversation, $request->user());

        return response()->json([
            'status'  => true,
            'message' => 'تم إغلاق المحادثة',
            'data'    => new ConversationResource($conversation->fresh()),
        ]);
    }

    /**
     * إعادة فتح المحادثة
     */
    public function reopen(Conversation $conversation, ChatService $chatService)
    {
        $chatService->reopen($conversation);

        return response()->json([
            'status'  => true,
            'message' => 'تم إعادة فتح المحادثة',
            'data'    => new ConversationResource($conversation->fresh()),
        ]);
    }

    /**
     * تعليم المحادثة كمقروءة للأدمن
     */
    public function markRead(Conversation $conversation, ChatService $chatService)
    {
        $chatService->markReadByAdmin($conversation);

        return response()->json([
            'status'  => true,
            'message' => 'تم تعليم المحادثة كمقروءة',
        ]);
    }

    /**
     * استلام المشرف للمحادثة (Join / Claim) وإصدار رسالة انضمام
     */
    public function claim(Request $request, Conversation $conversation, ChatService $chatService)
    {
        $chatService->claim($conversation, $request->user());

        return response()->json([
            'status'  => true,
            'message' => 'تم استلام المحادثة بنجاح',
            'data'    => new ConversationResource($conversation->fresh(['assignedTo:id,name'])),
        ]);
    }

    /**
     * جلب أرشيف المحادثات السابقة لمريض معين بسرعة عالية
     */
    public function patientHistory(Request $request, $patientId)
    {
        $query = Conversation::where(function($q) use ($patientId) {
            $q->where('patient_id', $patientId)->orWhere('user_id', $patientId);
        });

        if ($request->filled('status') && in_array($request->status, ['open', 'closed'])) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                if (is_numeric($search)) {
                    $q->where('id', $search);
                }
                $q->orWhere('last_message_preview', 'like', "%{$search}%");
            });
        }

        $totalCount = (clone $query)->count();
        $perPage = min((int) $request->input('per_page', 15), 50);
        $history = $query->latest('last_message_at')->cursorPaginate($perPage);

        return response()->json([
            'status' => true,
            'history' => $history->items(),
            'meta' => [
                'next_cursor' => $history->nextCursor()?->encode(),
                'has_more'    => $history->hasMorePages(),
                'total_count' => $totalCount,
                'per_page'    => $perPage,
            ],
        ]);
    }

    /**
     * ملف المريض الكامل من داخل الشات — بيانات + طلباته الأخيرة + إحصائيات
     * يُستخدم في Sidebar المحادثة لعرض سياق المريض بدون مغادرة الشات
     */
    public function patientProfile(Request $request, $patientId)
    {
        $patient = Patient::with([
            'district:id,name',
            'area:id,name',
            'chronicDiseases',
            'medications',
            'allergies',
        ])->findOrFail($patientId);

        // آخر 5 طلبات مع تفاصيلها الكاملة (الفني + الفرع + التحاليل + النتائج)
        $recentOrders = $patient->orders()
            ->with(['branch:id,name_ar', 'technician:id,name,phone', 'items', 'results'])
            ->latest()
            ->limit(5)
            ->get();

        // إحصائيات سريعة بدون جلب كل السجلات
        $stats = $patient->orders()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = "completed" THEN total ELSE 0 END) as total_spent
            ')
            ->first();

        return response()->json([
            'status'  => true,
            'patient' => new PatientResource($patient),
            'orders'  => OrderResource::collection($recentOrders),
            'orders_summary' => [
                'total'       => (int) ($stats->total ?? 0),
                'completed'   => (int) ($stats->completed ?? 0),
                'cancelled'   => (int) ($stats->cancelled ?? 0),
                'total_spent' => round((float) ($stats->total_spent ?? 0), 2),
            ],
        ]);
    }

    /**
     * الردود الجاهزة (Canned Responses)
     */
    public function cannedResponses()
    {
        return response()->json([
            'status' => true,
            'responses' => [
                [
                    'id' => 1,
                    'title' => 'ترحيب عام',
                    'body' => 'أهلاً بك في الدعم الفني لمختبر Healthy Lab، كيف يمكننا مساعدتك اليوم؟',
                ],
                [
                    'id' => 2,
                    'title' => 'تأكيد موعد السحب المنزلي',
                    'body' => 'تم تأكيد موعد سحب العينة المنزلية بنجاح، وسيصل الفني الميداني في الموعد المحدد.',
                ],
                [
                    'id' => 3,
                    'title' => 'الفني في الطريق للموقع',
                    'body' => 'الفني الميداني حالياً في الطريق إلى موقعك المسجل، يرجى الاستعداد والتأكد من إرشادات الصيام إن وجدت.',
                ],
                [
                    'id' => 4,
                    'title' => 'صدور نتيجة التحاليل الطبية',
                    'body' => 'تم اعتماد ورفع نتيجة التحليل الخاصة بك، يمكنك الاطلاع عليها وتحميل التقرير الطبي PDF عبر التطبيق.',
                ],
                [
                    'id' => 5,
                    'title' => 'طلب توضيح وتفاصيل إضافية',
                    'body' => 'يرجى تزويدنا برقم الطلب أو رقم الهاتف المسجل لنتمكن من متابعة حالة التحليل بشكل دقيق.',
                ],
                [
                    'id' => 6,
                    'title' => 'إغلاق ومتابعة',
                    'body' => 'يسعدنا دائماً خدمتكم في Healthy Lab. إذا كان لديك أي استفسار آخر في أي وقت لا تتردد بالتواصل معنا.',
                ],
            ],
        ]);
    }
}
