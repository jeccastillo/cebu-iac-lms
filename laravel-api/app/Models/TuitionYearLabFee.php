<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TuitionYearLabFee extends Model
{
    protected $table = 'tb_mas_tuition_year_lab_fee';
    protected $primaryKey = 'intID';
    public $timestamps = false;
    protected $guarded = [];
}
