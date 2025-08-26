<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TuitionYearTrack extends Model
{
    protected $table = 'tb_mas_tuition_year_track';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}
