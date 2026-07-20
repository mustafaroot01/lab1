<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class District extends Model
{
    protected $fillable = [
        'name',
        'governorate',
        'branch_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'branch_id'      => 'integer',
        'sort_order'     => 'integer',
        'is_active'      => 'boolean',
    ];

    public function getNameArAttribute(): ?string
    {
        return $this->name;
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
