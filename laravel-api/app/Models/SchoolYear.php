<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    protected $table = 'tb_mas_sy';
    protected $primaryKey = 'intID';
    public $timestamps = false;
    protected $guarded = [];
}
