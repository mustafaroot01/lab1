<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'status'          => $this->status,
            'status_label'    => $this->status_label,
            'status_color'    => $this->status_color,

            // أسعار الطلب
            'subtotal'        => $this->subtotal,
            'service_fee'     => $this->service_fee,
            'discount_amount' => $this->discount_amount,
            'total'           => $this->total,

            // تفاصيل الزيارة
            'visit_date'      => $this->visit_date?->toDateString(),
            'visit_time'      => $this->visit_time,
            'visit_period'    => $this->visit_period,
            'visit_period_label' => match ($this->visit_period) {
                'morning' => 'صباحاً',
                'noon'    => 'ظهراً',
                'evening' => 'مساءً',
                default   => $this->visit_period,
            },

            // الموقع
            'address_text' => $this->address_text,
            'lat'          => $this->lat,
            'lng'          => $this->lng,

            // معلومات إضافية
            'doctor_name'    => $this->doctor_name,
            'referral_image' => $this->referral_image
                ? (str_starts_with($this->referral_image, 'data:') || str_starts_with($this->referral_image, 'http') ? $this->referral_image : (str_contains($this->referral_image, '/storage/') ? asset('storage/' . ltrim(explode('/storage/', $this->referral_image)[1], '/')) : asset('storage/' . ltrim($this->referral_image, '/'))))
                : null,
            'notes'          => $this->notes,
            'cancel_reason'  => $this->cancel_reason,

            'patient'    => $this->whenLoaded('patient', function () {
                return [
                    'id'            => $this->patient->id,
                    'name'          => $this->patient->name,
                    'phone'         => $this->patient->phone,
                ];
            }),
            'user'       => $this->whenLoaded('patient', function () {
                return [
                    'id'            => $this->patient->id,
                    'name'          => $this->patient->name,
                    'phone'         => $this->patient->phone,
                ];
            }),
            'technician' => $this->whenLoaded('technician', fn() => [
                'id'    => $this->technician->id,
                'name'  => $this->technician->name,
                'phone' => $this->technician->phone ?? null,
            ]),
            'coupon'     => $this->whenLoaded('coupon', fn() => [
                'id'   => $this->coupon->id,
                'code' => $this->coupon->code,
            ]),
            'items'      => $this->whenLoaded('items', fn() => $this->items->map(fn($item) => [
                'id'        => $item->id,
                'item_type' => $item->item_type,
                'item_id'   => $item->item_id,
                'name_ar'   => $item->name_ar,
                'price'     => $item->price,
            ])),
            'items_count' => $this->whenCounted('items'),

            'status_logs' => $this->whenLoaded('statusLogs', fn() => $this->statusLogs->map(fn($log) => [
                'id'                => $log->id,
                'from_status'       => $log->from_status,
                'to_status'         => $log->to_status,
                'from_status_label' => $log->from_status_label,
                'to_status_label'   => $log->to_status_label,
                'notes'             => $log->notes,
                'changed_by_name'   => ($log->relationLoaded('changedBy') ? $log->changedBy?->name : null) ?? 'النظام / المشرف',
                'created_at'        => $log->created_at?->format('Y-m-d H:i:s'),

            ])),

            'results' => $this->whenLoaded('results', fn() => $this->results->map(fn($r) => [
                'id'         => $r->id,
                'file_name'  => $r->file_name,
                'file_type'  => $r->file_type,
                'file_size'  => $r->file_size,
                'url'        => asset('storage/' . $r->file_path),
                'created_at' => $r->created_at?->format('Y-m-d H:i'),
            ])),

            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
