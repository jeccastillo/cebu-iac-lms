<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ApplicantInterview model
 *
 * Table: tb_mas_applicant_interviews
 * Purpose: Stores a single interview record per applicant_data_id with schedule, interviewer, remarks,
 *          assessment (Passed/Failed), reason_for_failing, and completed_at timestamp.
 */
class ApplicantInterview extends Model
{
    protected $table = 'tb_mas_applicant_interviews';
    protected $primaryKey = 'id';
    public $timestamps = true;

    /**
     * Allowed assessment values.
     */
    public const ASSESSMENT_PASSED = 'Passed';
    public const ASSESSMENT_FAILED = 'Failed';

    protected $fillable = [
        'applicant_data_id',
        'scheduled_at',
        'interviewer_user_id',
        'remarks',
        'assessment',
        'reason_for_failing',
        'completed_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
