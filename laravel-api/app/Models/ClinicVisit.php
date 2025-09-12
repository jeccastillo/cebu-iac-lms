<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicVisit extends Model
{
    // Table name defaults to 'clinic_visits'

    protected $fillable = [
        'record_id',
        'visit_date',
        'reason',
        'triage',
        'assessment',
        'diagnosis_codes',
        'treatment',
        'medications_dispensed',
        'follow_up',
        'campus_id',
        'attachments_count',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
        'triage' => 'array',
        'diagnosis_codes' => 'array',
        'medications_dispensed' => 'array',
    ];

    /**
     * Parent health record.
     */
    public function record(): BelongsTo
    {
        return $this->belongsTo(ClinicHealthRecord::class, 'record_id');
    }

    /**
     * Attachments tied to this visit.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(ClinicAttachment::class, 'visit_id');
    }
}
