<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Actions\Orders\PreviewCartAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\PreviewCartRequest;

class CartController extends Controller
{
    /**
     * حساب وعرض ملخص السلة التفصيلي (Cart Preview & Breakdown)
     */
    public function previewCart(PreviewCartRequest $request, PreviewCartAction $action)
    {
        $result = $action->execute(
            (float) $request->subtotal,
            $request->branch_id,
            $request->coupon_code,
            $request->district_id
        );

        return response()->json($result);
    }
}
