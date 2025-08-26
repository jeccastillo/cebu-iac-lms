<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurriculumSubject extends Model
{
    protected $table = 'tb_mas_curriculum_subject';
    protected $primaryKey = 'intID';
    public $timestamps = false;
    protected $guarded = [];
}
