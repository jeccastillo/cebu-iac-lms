<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAdvisor extends Model
{
    protected $table = 'tb_mas_student_advisor';
    protected $primaryKey = 'intID';
    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [
        'intStudentID'    => 'integer',
        'intAdvisorID'    => 'integer',
        'assigned_by'     => 'integer',
        'campus_id'       => 'integer',
        'is_active'       => 'integer',
        'started_at'      => 'datetime',
        'ended_at'        => 'datetime',
        'department_code' => 'string',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'intStudentID', 'intID');
    }

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'intAdvisorID', 'intID');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'assigned_by', 'intID');
    }
}
