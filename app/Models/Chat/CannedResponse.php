<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class CannedResponse extends Model
{
    protected $table = 'canned_responses';

    protected $fillable = [
        'title',
        'content',
        'category',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
