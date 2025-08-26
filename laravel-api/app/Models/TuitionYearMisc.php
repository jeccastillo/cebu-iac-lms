<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TuitionYearMisc extends Model
{
    protected $table = 'tb_mas_tuition_year_misc';
    protected $primaryKey = 'intID';
    public $timestamps = false;
    protected $guarded = [];
}
