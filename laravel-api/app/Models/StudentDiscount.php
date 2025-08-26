<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDiscount extends Model
{
    protected $table = 'tb_mas_student_discount';
    protected $primaryKey = 'intID';
    public $timestamps = false;
    protected $guarded = [];
}
