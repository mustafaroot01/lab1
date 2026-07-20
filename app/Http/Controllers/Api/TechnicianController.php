<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTechnicianRequest;
use App\Http\Requests\UpdateTechnicianRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\TechnicianResource;
use App\Models\Order;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TechnicianController extends Controller
{
    /**
     * قائمة الفنيين مع تصفية وفرز وباجينيشن
     */
    public function index(Request $request)
    {
        $query = Technician::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('specialty', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Sort
        $sortBy  = in_array($request->input('sortBy'), ['id', 'name', 'phone', 'status', 'created_at'])
            ? $request->input('sortBy')
            : 'id';
        $orderBy = $request->input('orderBy') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $orderBy);

        $totalCount    = $query->count();
        $itemsPerPage  = (int) $request->input('itemsPerPage', 10);
        if ($itemsPerPage === -1) $itemsPerPage = max($totalCount, 1);

        $technicians = $query->withCount([
            'orders as total_orders_count',
            'orders as completed_orders_count' => function ($q) {
                $q->where('status', 'completed');
            },
            'orders as active_orders_count' => function ($q) {
                $q->whereIn('status', ['in_progress', 'sample_collected', 'on_the_way', 'technician_assigned']);
            },
        ])->paginate($itemsPerPage);


        // إحصائيات باستعلام واحد بدلاً من جلب كل السجلات في الذاكرة
        $stats = Technician::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended,
            SUM(CASE WHEN status = 'on_leave' THEN 1 ELSE 0 END) as on_leave
        ")->first();

        return response()->json([
            'status'           => true,
            'message'          => 'تم جلب قائمة الفنيين بنجاح',
            'technicians'      => TechnicianResource::collection($technicians->items()),
            'totalTechnicians' => $totalCount,
            'summary'          => [
                'total'     => (int) $stats->total,
                'active'    => (int) $stats->active,
                'suspended' => (int) $stats->suspended,
                'on_leave'  => (int) $stats->on_leave,
            ],
        ]);

    }

    /**
     * إضافة فني جديد
     */
    public function store(StoreTechnicianRequest $request)
    {
        $data = $request->validated();

        // Handle image uploads
        foreach (['id_front_image', 'id_back_image', 'district_id_image'] as $field) {
            if (!empty($data[$field]) && str_starts_with($data[$field], 'data:')) {
                $data[$field] = $this->saveBase64Image($data[$field], 'technicians');
            }
        }

        $technician = Technician::create($data);

        return response()->json([
            'status'     => true,
            'message'    => 'تم إضافة الفني بنجاح',
            'technician' => new TechnicianResource($technician),
        ], 201);
    }

    /**
     * عرض بيانات فني واحد مع طلباته وحالاتها وإحصائياته
     */
    public function show(Request $request, Technician $technician)
    {
        $ordersQuery = $technician->orders();

        $summary = [
            'total'          => (clone $ordersQuery)->count(),
            'completed'      => (clone $ordersQuery)->where('status', 'completed')->count(),
            'in_progress'    => (clone $ordersQuery)->whereIn('status', ['in_progress', 'sample_collected', 'on_the_way', 'technician_assigned'])->count(),
            'pending'        => (clone $ordersQuery)->whereIn('status', ['pending', 'confirmed', 'awaiting_technician'])->count(),
            'cancelled'      => (clone $ordersQuery)->where('status', 'cancelled')->count(),
            'total_earnings' => (clone $ordersQuery)->where('status', 'completed')->sum('service_fee'),
        ];

        // Filter by search query (id, user name, phone)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $ordersQuery->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->input('status') !== 'all') {
            if ($request->input('status') === 'in_progress_group') {
                $ordersQuery->whereIn('status', ['in_progress', 'sample_collected', 'on_the_way', 'technician_assigned']);
            } elseif ($request->input('status') === 'pending_group') {
                $ordersQuery->whereIn('status', ['pending', 'confirmed', 'awaiting_technician']);
            } else {
                $ordersQuery->where('status', $request->input('status'));
            }
        }

        $itemsPerPage = (int) $request->input('itemsPerPage', 10);
        if ($itemsPerPage === -1) {
            $itemsPerPage = max($summary['total'], 1);
        }

        $orders = (clone $ordersQuery)
            ->with(['patient.district', 'branch', 'items', 'statusLogs.changedBy'])
            ->latest()
            ->paginate($itemsPerPage);

        return response()->json([
            'status'         => true,
            'message'        => 'تم جلب بيانات الفني وطلبته بنجاح',
            'technician'     => new TechnicianResource($technician),
            'orders'         => OrderResource::collection($orders->items()),
            'totalOrders'    => $orders->total(),
            'currentPage'    => $orders->currentPage(),
            'lastPage'       => $orders->lastPage(),
            'orders_summary' => $summary,
        ]);
    }


    /**
     * تحديث بيانات الفني
     */
    public function update(UpdateTechnicianRequest $request, Technician $technician)
    {
        $data = $request->validated();

        // Remove password if empty
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Handle image uploads
        foreach (['id_front_image', 'id_back_image', 'district_id_image'] as $field) {
            if (!empty($data[$field]) && str_starts_with($data[$field], 'data:')) {
                $data[$field] = $this->saveBase64Image($data[$field], 'technicians');
            }
        }

        $technician->update($data);

        return response()->json([
            'status'     => true,
            'message'    => 'تم تحديث بيانات الفني بنجاح',
            'technician' => new TechnicianResource($technician->fresh()),
        ]);
    }

    /**
     * حذف الفني
     */
    public function destroy(Technician $technician)
    {
        $technician->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف الفني بنجاح',
        ]);
    }

    /**
     * تغيير حالة الفني يدعم: active / suspended / on_leave
     */
    public function toggleStatus(Request $request, Technician $technician)
    {
        $newStatus = $request->input('status');

        // If no explicit status passed, cycle between active ↔ suspended
        if (!in_array($newStatus, ['active', 'suspended', 'on_leave'])) {
            $newStatus = $technician->status === 'active' ? 'suspended' : 'active';
        }

        $technician->update(['status' => $newStatus]);

        $messages = [
            'active'    => 'تم تفعيل الفني',
            'suspended' => 'تم إيقاف الفني',
            'on_leave'  => 'تم تسجيل إجازة الفني',
        ];

        return response()->json([
            'status'     => true,
            'message'    => $messages[$newStatus] ?? 'تم تحديث الحالة',
            'technician' => new TechnicianResource($technician->fresh()),
        ]);
    }

    /**
     * حفظ صورة base64 على القرص
     */
    private function saveBase64Image(string $base64, string $folder): string
    {
        preg_match('/data:image\/(\w+);base64,/', $base64, $matches);
        $ext     = $matches[1] ?? 'jpg';
        $data    = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));
        $filename = $folder . '/' . uniqid() . '.' . $ext;
        Storage::disk('public')->put($filename, $data);

        return Storage::url($filename);
    }
}
