<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantType extends Model
{
    protected $table = 'tb_mas_applicant_types';
    protected $primaryKey = 'intID';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'type',
        'sub_type',
    ];
}
