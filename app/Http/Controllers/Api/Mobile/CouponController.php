<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\ValidateCouponRequest;
use App\Services\Orders\OrderProcessingService;

class CouponController extends Controller
{
    /**
     * التحقق من صحة كوبون وحساب السعر بعد الخصم
     */
    public function validateCoupon(ValidateCouponRequest $request, OrderProcessingService $orderService)
    {
        $result = $orderService->validateAndCalculateCoupon($request->code, (float) $request->subtotal);

        if (!$result['status']) {
            return response()->json(['status' => false, 'message' => $result['message']], $result['code']);
        }

        return response()->json($result['data']);
    }
}
