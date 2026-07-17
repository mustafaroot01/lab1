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
    public function execute(float $subtotal, ?int $branchId, ?string $couponCode, ?int $districtId = null): array
    {
        return $this->orderService->calculateCartPreview($subtotal, $branchId, $couponCode, $districtId);
    }
}
