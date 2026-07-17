<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TubeType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'code',
        'cap_color',
        'color_hex',
        'additive',
        'icon',
        'description',
        'sort_order',
    ];

    public function tests()
    {
        return $this->hasMany(MedicalTest::class, 'tube_type_id');
    }

    public function getTestsCountAttribute()
    {
        $count = $this->tests()->count();
        return $count > 0 ? $count : MedicalTest::where('tube_type', $this->name_ar)->count();
    }
}
