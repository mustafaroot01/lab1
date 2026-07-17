<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusLog extends Model
{
    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'notes',
    ];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function changedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Admin::class, 'changed_by_user_id');
    }

    // وصف الحالة السابقة باللغة العربية
    public function getFromStatusLabelAttribute(): string
    {
        if (!$this->from_status) return 'الإنشاء';
        return \App\Enums\OrderStatusEnum::tryFrom($this->from_status)?->label() ?? $this->from_status;
    }

    // وصف الحالة الجديدة باللغة العربية
    public function getToStatusLabelAttribute(): string
    {
        return \App\Enums\OrderStatusEnum::tryFrom($this->to_status)?->label() ?? $this->to_status;
    }
}
