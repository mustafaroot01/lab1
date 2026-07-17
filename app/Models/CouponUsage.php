<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id',
        'patient_id',
        'user_name',
        'phone',
        'discount_amount',
        'total_before_discount',
        'total_after_discount',
        'used_at',
    ];

    protected $casts = [
        'discount_amount' => 'float',
        'total_before_discount' => 'float',
        'total_after_discount' => 'float',
        'used_at' => 'datetime',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
