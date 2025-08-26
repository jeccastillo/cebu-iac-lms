<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $table = 'tb_mas_programs';
    protected $primaryKey = 'intProgramID';
    public $timestamps = false;

    protected $fillable = [
        'intProgramID',
        'strProgramDescription',
        'strProgramCode',
        'strMajor',
        'enumEnabled',
        'type',
        'short_name',
        'default_curriculum',
        'school',
        'campus_id',
    ];

    protected $casts = [
        'enumEnabled' => 'integer',
        'default_curriculum' => 'integer',
        'campus_id' => 'integer',
    ];
}
