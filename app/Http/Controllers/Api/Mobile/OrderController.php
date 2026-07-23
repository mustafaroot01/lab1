<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Actions\Orders\CancelOrderAction;
use App\Actions\Orders\CreateOrderAction;
use App\DTOs\Orders\CreateOrderDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\CancelOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * إنشاء طلب جديد (يتطلب تسجيل دخول)
     */
    public function store(StoreOrderRequest $request, CreateOrderAction $action)
    {
        $dto   = CreateOrderDTO::fromRequest($request);
        $order = $action->execute($dto);

        return response()->json([
            'status'  => true,
            'message' => 'تم تأكيد طلبك بنجاح! سيتواصل معك الفريق قريباً',
            'order'   => new OrderResource($order),
        ], 201);
    }

    /**
     * طلبات المريض "طلباتي" (يتطلب تسجيل دخول)
     */
    public function myOrders(Request $request)
    {
        $orders = Order::where(function($q) use ($request) {
                $q->where('patient_id', $request->user()->id)->orWhere('user_id', $request->user()->id);
            })
            ->with(['patient', 'items', 'technician'])
            ->withCount('items')
            ->latest()
            ->paginate(15);

        return response()->json([
            'status'  => true,
            'message' => 'تم جلب طلباتك بنجاح',
            'orders'  => OrderResource::collection($orders->items()),
            'meta'    => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    /**
     * تفاصيل طلب محدد
     */
    public function show(Request $request, $id)
    {
        $order = Order::where(function($q) use ($request) {
                $q->where('patient_id', $request->user()->id)->orWhere('user_id', $request->user()->id);
            })
            ->where('id', $id)
            ->with(['patient', 'items', 'technician', 'coupon', 'results', 'statusLogs.changedBy'])
            ->firstOrFail();


        return response()->json([
            'status' => true,
            'order'  => new OrderResource($order),
        ]);
    }

    /**
     * إلغاء الطلب (في حال pending فقط)
     */
    public function cancel(CancelOrderRequest $request, $id, CancelOrderAction $action)
    {
        $order = Order::where(function($q) use ($request) {
                $q->where('patient_id', $request->user()->id)->orWhere('user_id', $request->user()->id);
            })
            ->where('id', $id)
            ->firstOrFail();

        $action->execute($order, $request->user(), $request->cancel_reason);

        return response()->json([
            'status'  => true,
            'message' => 'تم إلغاء الطلب بنجاح',
        ]);
    }
}
