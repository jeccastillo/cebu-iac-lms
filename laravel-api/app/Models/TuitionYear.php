<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TuitionYear extends Model
{
    protected $table = 'tb_mas_tuition_year';
    protected $primaryKey = 'intID';
    public $timestamps = false;
    protected $guarded = [];
}
