<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'key',
        'icon',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tests()
    {
        return $this->hasMany(MedicalTest::class, 'test_group_id')->orderBy('sort_order', 'asc');
    }
}
