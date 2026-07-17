<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchService extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'fee',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'fee'       => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
