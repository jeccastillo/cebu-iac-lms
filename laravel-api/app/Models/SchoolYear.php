<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    protected $table = 'tb_mas_sy';
    protected $primaryKey = 'intID';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = [
        'midterm_start' => 'datetime',
        'midterm_end' => 'datetime',
        'final_start' => 'datetime',
        'final_end' => 'datetime',
        'end_of_submission' => 'datetime',

        // Extended date-like fields
        'start_of_classes' => 'datetime',
        'final_exam_start' => 'datetime',
        'final_exam_end' => 'datetime',

        'viewing_midterm_start' => 'datetime',
        'viewing_midterm_end' => 'datetime',
        'viewing_final_start' => 'datetime',
        'viewing_final_end' => 'datetime',

        'endOfApplicationPeriod' => 'datetime',
        'reconf_start' => 'datetime',
        'reconf_end' => 'datetime',
        'ar_report_date_generation' => 'datetime',

        'installment1' => 'datetime',
        'installment2' => 'datetime',
        'installment3' => 'datetime',
        'installment4' => 'datetime',
        'installment5' => 'datetime',
    ];
}
