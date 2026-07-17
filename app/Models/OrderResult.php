<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderResult extends Model
{
    protected $fillable = [
        'order_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'uploaded_by_user_id',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function uploadedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Admin::class, 'uploaded_by_user_id');
    }
}
