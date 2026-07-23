<?php

namespace App\Actions\Orders;

use App\Services\Orders\OrderProcessingService;

class PreviewCartAction
{
    public function __construct(private OrderProcessingService $orderService)
    {
    }

    /**
     * تنفيذ حسابات ملخص السلة وأجور الزيارة والخصم
     */
    public function execute(float $subtotal, ?string $couponCode, ?float $lat = null, ?float $lng = null, bool $hasLabItems = false): array
    {
        return $this->orderService->calculateCartPreview($subtotal, $couponCode, $lat, $lng, $hasLabItems);
    }
}
