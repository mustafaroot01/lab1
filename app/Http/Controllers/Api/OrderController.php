<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\UpdateAdminOrderStatusRequest;
use App\Http\Requests\Orders\UploadOrderResultRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderResult;
use App\Models\OrderStatusLog;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Events\OrderStatusChanged;
use App\Enums\NotificationType;


class OrderController extends Controller
{
    /**
     * قائمة جميع الطلبات (لوحة التحكم)
     */
    public function index(Request $request)
    {
        $query = \App\Queries\OrderSearch::run($request)
            ->with([
                'district:id,name',
                'patient:id,name,phone,district_id',
                'patient.district:id,name',
                'branch:id,name_ar,phone',
                'technician:id,name,phone'
            ])
            ->withCount('items')
            ->latest();

        $totalCount = $query->count();
        $itemsPerPage = (int) $request->input('itemsPerPage', 10);
        if ($itemsPerPage === -1 || $itemsPerPage > 500) {
            $itemsPerPage = max($totalCount, 1);
        }

        $orders = $query->paginate($itemsPerPage);

        return response()->json([
            'status'      => true,
            'message'     => 'تم جلب الطلبات بنجاح',
            'orders'      => OrderResource::collection($orders->items()),
            'totalOrders' => $orders->total(),
            'meta'        => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'total'        => $orders->total(),
            ],
            'summary' => $this->buildStatusSummary(),
        ]);
    }

    /**
     * بناء ملخص عدد الطلبات لكل حالة اعتماداً على الحالات المعتمدة في النموذج
     */
    private function buildStatusSummary(): array
    {
        $counts = Order::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $summary = [];
        foreach (Order::STATUSES as $status) {
            $summary[$status] = (int) ($counts[$status] ?? 0);
        }

        return $summary;
    }

    /**
     * تفاصيل طلب محدد
     */
    public function show(Order $order)
    {
        $order->load(['district', 'patient.district', 'branch', 'technician', 'coupon', 'items', 'statusLogs.changedBy', 'results']);

        return response()->json([
            'status' => true,
            'order'  => new OrderResource($order),
        ]);
    }

    /**
     * تحديث حالة الطلب + تعيين الفني
     */
    public function updateStatus(UpdateAdminOrderStatusRequest $request, Order $order)
    {
        $oldStatus = $order->status;
        $oldTechId = $order->technician_id;

        $data = ['status' => $request->status];

        if ($request->filled('technician_id')) {
            $data['technician_id'] = $request->technician_id;
        }

        if ($request->status === 'cancelled' && $request->filled('cancel_reason')) {
            $data['cancel_reason'] = $request->cancel_reason;
        }

        $order->update($data);

        // تسجيل في سجل الحالات إذا تغيرت الحالة أو تغير الفني
        if ($oldStatus !== $request->status || $oldTechId != $order->technician_id) {
            $notes = [];
            if ($oldTechId != $order->technician_id) {
                $techName = $order->technician?->name ?? 'بدون تعيين';
                $notes[] = "تم تعيين / تغيير الفني إلى: {$techName}";
            }
            if ($request->status === 'cancelled' && $request->filled('cancel_reason')) {
                $notes[] = "سبب الإلغاء: {$request->cancel_reason}";
            }

            OrderStatusLog::create([
                'order_id'           => $order->id,
                'from_status'        => $oldStatus,
                'to_status'          => $request->status,
                'changed_by_user_id' => $request->user()?->id,
                'notes'              => !empty($notes) ? implode(' - ', $notes) : null,
            ]);

            // Dispatch notification event
            $notificationType = NotificationType::tryFrom($request->status);
            if ($notificationType) {
                event(new OrderStatusChanged($order, $notificationType));
            }
        }

        $order->load(['patient.district', 'branch', 'technician', 'coupon', 'items', 'statusLogs.changedBy', 'results']);

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث حالة الطلب بنجاح',
            'order'   => new OrderResource($order),
        ]);
    }

    /**
     * رفع نتيجة تحليل (صورة أو ملف PDF) للطلب
     */
    public function storeResult(UploadOrderResultRequest $request, Order $order)
    {
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());
        $fileType = $ext === 'pdf' ? 'pdf' : 'image';
        $fileSize = $file->getSize();

        $path = $file->store("order_results/{$order->id}", 'public');

        $result = $order->results()->create([
            'file_path'           => $path,
            'file_name'           => $fileName,
            'file_type'           => $fileType,
            'file_size'           => $fileSize,
            'uploaded_by_user_id' => $request->user()?->id,
        ]);

        // تسجيل في الخط الزمني
        OrderStatusLog::create([
            'order_id'           => $order->id,
            'from_status'        => $order->status,
            'to_status'          => $order->status,
            'changed_by_user_id' => $request->user()?->id,
            'notes'              => "تم رفع نتيجة تحليل للمراجع: {$fileName}",
        ]);

        // Dispatch result ready notification
        event(new OrderStatusChanged($order, NotificationType::RESULT_READY));

        $order->load(['patient.district', 'branch', 'technician', 'coupon', 'items', 'statusLogs.changedBy', 'results']);

        return response()->json([
            'status'  => true,
            'message' => 'تم رفع ملف نتيجة التحليل بنجاح وسيظهر للمراجع',
            'order'   => new OrderResource($order),
        ]);
    }

    /**
     * حذف نتيجة تحليل مرفوعة
     */
    public function destroyResult(Order $order, OrderResult $result)
    {
        if ($result->order_id !== $order->id) {
            return response()->json(['status' => false, 'message' => 'الملف غير تابع لهذا الطلب'], 403);
        }

        Storage::disk('public')->delete($result->file_path);
        $resultName = $result->file_name;
        $result->delete();

        OrderStatusLog::create([
            'order_id'           => $order->id,
            'from_status'        => $order->status,
            'to_status'          => $order->status,
            'changed_by_user_id' => request()->user()?->id,
            'notes'              => "تم حذف ملف نتيجة التحليل: {$resultName}",
        ]);

        $order->load(['patient.district', 'branch', 'technician', 'coupon', 'items', 'statusLogs.changedBy', 'results']);

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف ملف النتيجة بنجاح',
            'order'   => new OrderResource($order),
        ]);
    }
}
