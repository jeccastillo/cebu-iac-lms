<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicHealthRecord extends Model
{
    // Table name defaults to 'clinic_health_records' based on class name.

    protected $fillable = [
        'person_type',
        'person_student_id',
        'person_faculty_id',
        'blood_type',
        'height_cm',
        'weight_kg',
        'allergies',
        'medications',
        'immunizations',
        'conditions',
        'notes',
        'campus_id',
        'last_updated_by',
    ];

    protected $casts = [
        'height_cm' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'allergies' => 'array',
        'medications' => 'array',
        'immunizations' => 'array',
        'conditions' => 'array',
    ];

    /**
     * Visits under this health record.
     */
    public function visits(): HasMany
    {
        return $this->hasMany(ClinicVisit::class, 'record_id');
    }

    /**
     * Attachments linked directly to the health record.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(ClinicAttachment::class, 'record_id');
    }
}
