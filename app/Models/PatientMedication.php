<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientMedication extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'user_id',
        'medication_name',
        'dosage',
        'frequency',
        'start_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
        ];
    }

    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

}
