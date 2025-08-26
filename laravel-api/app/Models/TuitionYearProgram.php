<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TuitionYearProgram extends Model
{
    protected $table = 'tb_mas_tuition_year_program';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}
