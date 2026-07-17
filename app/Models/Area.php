<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Area extends Model
{
    protected $fillable = [
        'district_id',
        'name',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'district_id' => 'integer',
        'sort_order'  => 'integer',
        'is_active'   => 'boolean',
    ];

    public function getNameArAttribute(): ?string
    {
        return $this->name;
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
