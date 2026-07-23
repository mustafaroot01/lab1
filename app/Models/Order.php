<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    /**
     * الحالات المعتمدة للطلب — مصدر واحد للحقيقة يُستخدم في التحقق والملخصات والواجهة
     */
    public const STATUSES = [
        'pending',
        'confirmed',
        'awaiting_technician',
        'technician_assigned',
        'on_the_way',
        'sample_collected',
        'in_progress',
        'completed',
        'cancelled',
    ];

    protected $fillable = [
        'patient_id',
        'user_id',
        'technician_id',
        'coupon_id',
        'status',
        'subtotal',
        'service_fee',
        'discount_amount',
        'total',
        'visit_date',
        'visit_time',
        'visit_period',
        'address_text',
        'doctor_name',
        'referral_image',
        'notes',
        'cancel_reason',
        'lat',
        'lng',
        'coverage_zone_id',
        'coverage_zone_snapshot',
        'building',
        'floor',
        'apartment',
        'landmark',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'        => 'float',
            'service_fee'     => 'float',
            'discount_amount' => 'float',
            'total'           => 'float',
            'visit_date'      => 'date',
        ];
    }

    protected $appends = ['status_label', 'status_color'];

    // ─── علاقات ─────────────────────────────────────────
    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function technician(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public function coupon(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->latest();
    }

    public function results(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderResult::class)->latest();
    }

    // ─── وصف الحالة ─────────────────────────────────────
    public function getStatusLabelAttribute(): string
    {
        return \App\Enums\OrderStatusEnum::tryFrom($this->status)?->label() ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return \App\Enums\OrderStatusEnum::tryFrom($this->status)?->color() ?? 'default';
    }
}
