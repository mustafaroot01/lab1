<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponUsageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'coupon_id' => $this->coupon_id,
            'patient_id' => $this->patient_id,
            'user_name' => $this->user_name,
            'phone' => $this->phone,
            'discount_amount' => (float) $this->discount_amount,
            'total_before_discount' => (float) $this->total_before_discount,
            'total_after_discount' => (float) $this->total_after_discount,
            'used_at' => $this->used_at ? $this->used_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
