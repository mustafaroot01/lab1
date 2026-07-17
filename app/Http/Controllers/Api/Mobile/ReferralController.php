<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\UploadReferralRequest;
use App\Services\Orders\OrderProcessingService;

class ReferralController extends Controller
{
    /**
     * رفع صورة الراجعة الطبية (الروشتة)
     */
    public function uploadReferralImage(UploadReferralRequest $request, OrderProcessingService $orderService)
    {
        $result = $orderService->storeReferralImage($request->file('image'));

        return response()->json($result);
    }
}
