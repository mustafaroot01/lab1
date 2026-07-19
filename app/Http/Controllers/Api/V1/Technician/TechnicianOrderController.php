<?php

namespace App\Http\Controllers\Api\V1\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\UpdateTechnicianOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderStatusLog;
use Illuminate\Http\Request;
use App\Events\OrderStatusChanged;
use App\Enums\NotificationType;


class TechnicianOrderController extends Controller
{
    /**
     * استعراض الطلبات المسندة لهذا الفني الميداني فقط
     */
    public function myOrders(Request $request)
    {
        /** @var \App\Models\Technician $technician */
        $technician = $request->user();

        $query = $technician->orders()
            ->with([
                'patient:id,name,phone,district_id,area_id',
                'patient.district:id,name',
                'patient.area:id,name',
                'branch:id,name_ar',
                'technician:id,name',
                'items',
            ])
            ->withCount('items');

        // فلترة اختيارية حسب حالة الزيارة
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->whereIn('status', ['technician_assigned', 'on_the_way', 'in_progress']);
            } elseif ($status === 'completed') {
                $query->whereIn('status', ['sample_collected', 'completed', 'results_ready']);
            } else {
                $query->where('status', $status);
            }
        }

        $orders = $query->latest()->paginate(15);

        return response()->json([
            'status'  => true,
            'message' => 'تم جلب الزيارات المسندة للفني بنجاح',
            'orders'  => OrderResource::collection($orders->items()),
            'meta'    => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    /**
     * تفاصيل زيارة محددة وموقع المراجع GPS للوصول السريع
     */
    public function show(Request $request, $id)
    {
        /** @var \App\Models\Technician $technician */
        $technician = $request->user();

        $order = $technician->orders()
            ->where('id', $id)
            ->with([
                'patient:id,name,phone,district_id,area_id',
                'patient.district:id,name',
                'patient.area:id,name',
                'branch:id,name_ar',
                'technician:id,name,phone',
                'coupon:id,code',
                'items',
                'statusLogs.changedBy',
                'results',
            ])
            ->firstOrFail();

        return response()->json([
            'status'  => true,
            'message' => 'تم جلب تفاصيل الزيارة بنجاح',
            'data'    => [
                'order' => new OrderResource($order),
            ],
        ]);
    }

    /**
     * تحديث حالة الزيارة الميدانية (في الطريق أو تم سحب العينة) وإضافة الإحداثيات الحية
     */
    public function updateStatus(UpdateTechnicianOrderStatusRequest $request, $id)
    {
        /** @var \App\Models\Technician $technician */
        $technician = $request->user();

        /** @var Order $order */
        $order = $technician->orders()->where('id', $id)->firstOrFail();

        $oldStatus = $order->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return response()->json([
                'status'  => true,
                'message' => 'حالة الطلب الحالية هي بالفعل نفس الحالة المحددة',
                'order'   => new OrderResource($order->load(['patient.district', 'patient.area', 'branch', 'technician', 'items'])),
            ]);
        }

        // تحديث حالة الطلب وإحداثيات الموقع إن تم إرسالها من الموبايل
        $updateData = ['status' => $newStatus];
        if ($request->filled('lat') && $request->filled('lng')) {
            $updateData['lat'] = $request->lat;
            $updateData['lng'] = $request->lng;
        }

        $order->update($updateData);

        // تسجيل الحركة في سجل حالات الطلب Status Logs
        OrderStatusLog::create([
            'order_id'           => $order->id,
            'from_status'        => $oldStatus,
            'to_status'          => $newStatus,
            'changed_by_user_id' => null, // تم التغيير عبر الفني الميداني
            'notes'              => $request->notes ?: ("تم تحديث حالة الزيارة إلى: " . ($newStatus === 'on_the_way' ? 'الفني في الطريق للمراجع' : 'تم سحب العينة ميدانياً') . " بواسطة الفني: {$technician->name}"),
        ]);

        // Dispatch notification event
        $notificationType = NotificationType::tryFrom($newStatus);
        if ($notificationType) {
            event(new OrderStatusChanged($order, $notificationType));
        }

        // تحميل العلاقات لمنع Lazy Loading Exceptions أثناء إرجاع الـ Resource
        $order->load([
            'patient:id,name,phone,district_id,area_id',
            'patient.district:id,name',
            'patient.area:id,name',
            'branch:id,name_ar',
            'technician:id,name,phone',
            'items',
            'statusLogs.changedBy',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث حالة الزيارة الميدانية بنجاح',
            'data'    => [
                'order' => new OrderResource($order),
            ],
        ]);
    }
}
