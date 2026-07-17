<?php

namespace App\DTOs\Orders;

use App\Models\User;
use Illuminate\Http\Request;

class CreateOrderDTO
{
    public function __construct(
        public readonly User $user,
        public readonly ?int $branchId,
        public readonly ?int $districtId,
        public readonly ?int $areaId,
        public readonly ?string $couponCode,
        public readonly array $items,
        public readonly string $visitDate,
        public readonly string $visitTime,
        public readonly string $visitPeriod,
        public readonly ?float $lat,
        public readonly ?float $lng,
        public readonly ?string $addressText,
        public readonly ?string $doctorName,
        public readonly ?string $referralImage,
        public readonly ?string $notes,
    ) {}

    /**
     * بناء الـ DTO مباشرة من الطلب ومرسل الطلب الموثق
     */
    public static function fromRequest(Request $request): self
    {
        $user = $request->user();
        return new self(
            user: $user,
            branchId: $request->filled('branch_id') ? (int) $request->branch_id : null,
            districtId: $request->filled('district_id') ? (int) $request->district_id : ($user->district_id ?? null),
            areaId: $request->filled('area_id') ? (int) $request->area_id : ($user->area_id ?? null),
            couponCode: $request->filled('coupon_code') ? strtoupper(trim($request->coupon_code)) : null,
            items: is_array($request->items) ? $request->items : [],
            visitDate: (string) $request->visit_date,
            visitTime: (string) $request->visit_time,
            visitPeriod: (string) $request->visit_period,
            lat: $request->filled('lat') ? (float) $request->lat : null,
            lng: $request->filled('lng') ? (float) $request->lng : null,
            addressText: $request->input('address_text'),
            doctorName: $request->input('doctor_name'),
            referralImage: $request->input('referral_image'),
            notes: $request->input('notes'),
        );
    }
}
