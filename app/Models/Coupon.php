<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'discount_type',
        'discount_value',
        'start_date',
        'end_date',
        'usage_limit',
        'used_count',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'discount_value' => 'float',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    protected $appends = ['status'];

    public function usages()
    {
        return $this->hasMany(CouponUsage::class)->orderBy('used_at', 'desc');
    }

    public function getStatusAttribute()
    {
        if (!$this->is_active) {
            return 'inactive'; // موقوف يدوياً
        }

        if ($this->end_date && Carbon::now()->greaterThan($this->end_date)) {
            return 'expired_time'; // منتهي الصلاحية لانتهاء الوقت
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return 'expired_limit'; // منتهي الصلاحية لاكتمال الحد الأقصى للاستخدام
        }

        if ($this->start_date && Carbon::now()->lessThan($this->start_date)) {
            return 'upcoming'; // لم يبدأ بعد
        }

        return 'active'; // فعّال ومتاح للاستخدام
    }
}
