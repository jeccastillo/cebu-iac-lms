<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $table = 'tb_mas_classrooms';
    protected $primaryKey = 'intID';
    public $timestamps = false;
    protected $guarded = [];
}
