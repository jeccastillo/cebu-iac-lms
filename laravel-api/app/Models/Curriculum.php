<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curriculum extends Model
{
    protected $table = 'tb_mas_curriculum';
    protected $primaryKey = 'intID';
    public $timestamps = false;
    protected $guarded = [];
}
