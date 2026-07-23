<?php

namespace App\DTOs\Orders;

use App\Models\User;
use Illuminate\Http\Request;

class CreateOrderDTO
{
    public function __construct(
        public readonly User|\App\Models\Patient $user,
        public readonly ?string $couponCode,
        public readonly array $items,
        public readonly string $visitDate,
        public readonly string $visitTime,
        public readonly string $visitPeriod,
        public readonly ?string $addressText,
        public readonly ?string $doctorName,
        public readonly ?string $referralImage,
        public readonly ?string $notes,
        
        // New spatial fields
        public readonly ?float $lat = null,
        public readonly ?float $lng = null,
        public readonly ?string $building = null,
        public readonly ?string $floor = null,
        public readonly ?string $apartment = null,
        public readonly ?string $landmark = null,
    ) {}

    /**
     * بناء الـ DTO مباشرة من الطلب ومرسل الطلب الموثق
     */
    public static function fromRequest(Request $request): self
    {
        $user = $request->user();
        return new self(
            user: $user,
            couponCode: $request->filled('coupon_code') ? strtoupper(trim($request->coupon_code)) : null,
            items: is_array($request->items) ? $request->items : [],
            visitDate: (string) $request->visit_date,
            visitTime: (string) $request->visit_time,
            visitPeriod: (string) $request->visit_period,
            addressText: $request->input('address_text') ?: ($user->address ?? null),
            doctorName: $request->input('doctor_name'),
            referralImage: $request->input('referral_image'),
            notes: $request->input('notes'),
            
            lat: $request->filled('lat') ? (float) $request->lat : null,
            lng: $request->filled('lng') ? (float) $request->lng : null,
            building: $request->input('building'),
            floor: $request->input('floor'),
            apartment: $request->input('apartment'),
            landmark: $request->input('landmark'),
        );
    }
}
