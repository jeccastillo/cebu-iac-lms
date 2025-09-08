<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationAwareness extends Model
{
    protected $table = 'tb_mas_application_awareness';

    protected $fillable = [
        'applicant_data_id',
        'name',
        'sub_name',
        'referral',
        'name_of_referee',
    ];

    // Timestamps enabled by default
}
