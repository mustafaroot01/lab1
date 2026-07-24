<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat\Conversation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Patient;
use App\Models\Technician;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * جلب كافة إحصائيات وبيانات لوحة التحكم الرئيسية للمختبر (Command Center)
     */
    public function stats(Request $request)
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->startOfMonth()->subSecond();

        // ── 1. شريط المؤشرات الحيوية (KPI Metrics) ──
        $todayOrdersStats = Order::whereDate('created_at', $today)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status IN ("pending", "confirmed", "awaiting_technician") THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status IN ("technician_assigned", "on_the_way", "sample_collected", "in_progress") THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled
            ')
            ->first();

        $yesterdayOrdersCount = Order::whereDate('created_at', $yesterday)->count();
        $ordersGrowth = $yesterdayOrdersCount > 0
            ? round((($todayOrdersStats->total - $yesterdayOrdersCount) / $yesterdayOrdersCount) * 100, 1)
            : ($todayOrdersStats->total > 0 ? 100 : 0);

        // الفنيون في الميدان
        $totalTechnicians = Technician::count();
        $activeTechnicians = Technician::where('status', 'active')->count();
        $busyTechnicians = Order::whereIn('status', ['technician_assigned', 'on_the_way', 'sample_collected', 'in_progress'])
            ->whereNotNull('technician_id')
            ->distinct('technician_id')
            ->count('technician_id');

        // تذاكر الدعم الفني
        $activeTicketsCount = Conversation::where('status', 'open')->count();
        $closedTicketsCount = Conversation::where('status', 'closed')->count();

        // الإيرادات المالية
        $todayRevenue = Order::whereDate('created_at', $today)->where('status', 'completed')->sum('total');
        $monthRevenue = Order::where('created_at', '>=', $startOfMonth)->where('status', 'completed')->sum('total');
        $lastMonthRevenue = Order::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->where('status', 'completed')->sum('total');

        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : ($monthRevenue > 0 ? 100 : 0);

        $kpi = [
            'today_orders' => [
                'total'     => (int) ($todayOrdersStats->total ?? 0),
                'pending'   => (int) ($todayOrdersStats->pending ?? 0),
                'active'    => (int) ($todayOrdersStats->active ?? 0),
                'completed' => (int) ($todayOrdersStats->completed ?? 0),
                'cancelled' => (int) ($todayOrdersStats->cancelled ?? 0),
                'growth'    => $ordersGrowth,
            ],
            'technicians' => [
                'active' => $activeTechnicians,
                'busy'   => $busyTechnicians,
                'total'  => $totalTechnicians,
            ],
            'tickets' => [
                'open'   => $activeTicketsCount,
                'closed' => $closedTicketsCount,
            ],
            'revenue' => [
                'today'         => round((float) $todayRevenue, 2),
                'month'         => round((float) $monthRevenue, 2),
                'last_month'    => round((float) $lastMonthRevenue, 2),
                'growth'        => $revenueGrowth,
            ],
        ];

        // ── 2. الطلبات الحرجة والمستعجلة (Urgent Orders) ──
        $urgentOrders = Order::with([
            'patient:id,name,phone',
            'technician:id,name,phone',
        ])
            ->whereIn('status', ['awaiting_technician', 'pending', 'sample_collected', 'in_progress'])
            ->orderByRaw('CASE
                WHEN status = "awaiting_technician" THEN 1
                WHEN status = "sample_collected" THEN 2
                WHEN status = "pending" THEN 3
                ELSE 4 END')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($order) => [
                'id'             => $order->id,
                'status'         => $order->status,
                'status_label'   => $order->status_label,
                'status_color'   => $order->status_color,
                'patient_name'   => $order->patient?->name ?? 'غير محدد',
                'patient_phone'  => $order->patient?->phone ?? '—',
                'branch_name'    => 'الفرع الرئيسي',
                'technician'     => $order->technician ? [
                    'id'    => $order->technician->id,
                    'name'  => $order->technician->name,
                    'phone' => $order->technician->phone,
                ] : null,
                'visit_date'     => $order->visit_date?->format('Y-m-d'),
                'visit_time'     => $order->visit_time,
                'total'          => round((float) $order->total, 2),
                'created_at'     => $order->created_at?->format('Y-m-d H:i'),
            ]);

        // ── 3. الرسوم البيانية (Charts Data) ──
        // رسم 7 أيام
        $flowDates = [];
        $flowCompleted = [];
        $flowActive = [];
        $flowCancelled = [];
        $flowRevenue = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $flowDates[] = $date->locale('ar')->isoFormat('dddd (D/M)');

            $dayStats = Order::whereDate('created_at', $dateStr)
                ->selectRaw('
                    SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status IN ("pending", "confirmed", "awaiting_technician", "technician_assigned", "on_the_way", "sample_collected", "in_progress") THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN status = "completed" THEN total ELSE 0 END) as revenue
                ')
                ->first();

            $flowCompleted[] = (int) ($dayStats->completed ?? 0);
            $flowActive[]    = (int) ($dayStats->active ?? 0);
            $flowCancelled[] = (int) ($dayStats->cancelled ?? 0);
            $flowRevenue[]   = round((float) ($dayStats->revenue ?? 0), 0);
        }

        // أكثر الفحوصات والباقات طلباً
        $topTests = OrderItem::select('name_ar', DB::raw('COUNT(*) as count'), DB::raw('SUM(price) as revenue'))
            ->groupBy('name_ar')
            ->orderByDesc('count')
            ->limit(6)
            ->get()
            ->map(fn ($item) => [
                'name'    => $item->name_ar,
                'count'   => (int) $item->count,
                'revenue' => round((float) $item->revenue, 0),
            ]);

        // توزيع الطلبات على الفروع (تم إلغاء الفروع - سيتم إرجاع الفرع الرئيسي فقط أو مصفوفة فارغة)
        $branchDistribution = collect([
            [
                'id'               => 1,
                'name'             => 'الفرع الرئيسي',
                'total_orders'     => Order::count(),
                'completed_orders' => Order::where('status', 'completed')->count(),
                'revenue'          => round((float) Order::where('status', 'completed')->sum('total'), 0),
            ]
        ]);

        // ── 4. أكفأ الفنيين (Top Technicians) ──
        $topTechnicians = Technician::where('status', 'active')
            ->withCount([
                'orders as completed_month_count' => fn ($q) => $q->where('status', 'completed')->where('created_at', '>=', $startOfMonth),
                'orders as active_orders_count' => fn ($q) => $q->whereIn('status', ['technician_assigned', 'on_the_way', 'sample_collected', 'in_progress']),
            ])
            ->orderByDesc('completed_month_count')
            ->limit(5)
            ->get()
            ->map(fn ($t) => [
                'id'               => $t->id,
                'name'             => $t->name,
                'phone'            => $t->phone,
                'specialty'        => $t->specialty ?? 'فني سحب عينات',
                'completed_month'  => (int) $t->completed_month_count,
                'is_busy'          => $t->active_orders_count > 0,
            ]);

        // ── 5. شريط الأحداث المباشر (Recent Timeline) ──
        $recentLogs = OrderStatusLog::with(['order.patient', 'changedBy'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn ($log) => [
                'id'          => 'log_' . $log->id,
                'type'        => 'order_log',
                'title'       => 'تحديث حالة الطلب #' . $log->order_id,
                'description' => sprintf(
                    'المريض (%s): تم تغيير الحالة من "%s" إلى "%s"',
                    $log->order?->patient?->name ?? 'مريض',
                    $log->from_status_label ?? '—',
                    $log->to_status_label ?? '—'
                ),
                'time'        => $log->created_at?->diffForHumans() ?? '',
                'timestamp'   => $log->created_at?->timestamp ?? 0,
                'icon'        => 'tabler-refresh',
                'color'       => $log->to_status === 'completed' ? 'success' : ($log->to_status === 'cancelled' ? 'error' : 'primary'),
            ]);

        $recentConversations = Conversation::with('patient:id,name')
            ->latest('last_message_at')
            ->limit(4)
            ->get()
            ->map(fn ($conv) => [
                'id'          => 'conv_' . $conv->id,
                'type'        => 'ticket',
                'title'       => 'تذكرة دعم فني #' . $conv->id,
                'description' => sprintf('المريض (%s): %s', $conv->patient?->name ?? 'مريض', $conv->last_message_preview ?? 'رسالة جديدة'),
                'time'        => $conv->last_message_at?->diffForHumans() ?? ($conv->created_at?->diffForHumans() ?? ''),
                'timestamp'   => $conv->last_message_at?->timestamp ?? ($conv->created_at?->timestamp ?? 0),
                'icon'        => 'tabler-messages',
                'color'       => $conv->status === 'open' ? 'info' : 'secondary',
            ]);

        $timeline = collect($recentLogs)
            ->concat($recentConversations)
            ->sortByDesc('timestamp')
            ->take(8)
            ->values();

        return response()->json([
            'status' => true,
            'data'   => [
                'kpi'                 => $kpi,
                'urgent_orders'       => $urgentOrders,
                'charts'              => [
                    'flow' => [
                        'dates'     => $flowDates,
                        'completed' => $flowCompleted,
                        'active'    => $flowActive,
                        'cancelled' => $flowCancelled,
                        'revenue'   => $flowRevenue,
                    ],
                    'top_tests'           => $topTests,
                    'branch_distribution' => $branchDistribution,
                ],
                'top_technicians'     => $topTechnicians,
                'timeline'            => $timeline,
            ],
        ]);
    }
}
