<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientAllergy extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'user_id',
        'allergen',
        'severity',
        'reaction',
        'notes',
    ];

    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

}
