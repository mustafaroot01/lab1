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
            $request->coupon_code,
            $request->filled('lat') ? (float) $request->lat : null,
            $request->filled('lng') ? (float) $request->lng : null,
            $request->hasLabItems(),  // هل السلة تحتوي تحاليل مخبرية؟
        );

        return response()->json($result);
    }
}
