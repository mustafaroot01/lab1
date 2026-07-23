<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'notification_type',
        'notifiable_type',
        'notifiable_id',
        'status',
        'response',
        'error',
        'sent_at'
    ];

    protected $casts = [
        'response' => 'array',
        'sent_at' => 'datetime'
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }
}
