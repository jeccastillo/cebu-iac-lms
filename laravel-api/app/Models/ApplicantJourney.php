<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantJourney extends Model
{
    protected $table = 'tb_mas_applicant_journey';

    // No created_at/updated_at columns
    public $timestamps = false;

    protected $fillable = [
        'applicant_data_id',
        'remarks',
        'log_date',
    ];
}
