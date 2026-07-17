<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'is_active',
        'last_updated_at',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'last_updated_at' => 'datetime',
    ];
}
