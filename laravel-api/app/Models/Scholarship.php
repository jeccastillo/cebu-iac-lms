<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    protected $table = 'tb_mas_scholarships';
    protected $primaryKey = 'intID';
    public $timestamps = false;
    protected $guarded = [];
}
