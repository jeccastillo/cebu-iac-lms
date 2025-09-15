<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftRequest extends Model
{
    protected $table = 'tb_mas_shift_requests';

    protected $fillable = [
        'student_id',
        'student_number',
        'term_id',
        'program_from',
        'program_to',
        'reason',
        'status',
        'requested_at',
        'processed_at',
        'processed_by_faculty_id',
        'campus_id',
        'meta',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'meta'         => 'array',
    ];
}
