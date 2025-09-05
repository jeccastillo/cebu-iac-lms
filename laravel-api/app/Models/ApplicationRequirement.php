<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationRequirement extends Model
{
    protected $table = 'tb_mas_application_requirements';
    protected $primaryKey = 'intID';
    public $timestamps = true;

    protected $fillable = [
        'intStudentID',
        'tb_mas_requirements_id',
        'submitted_status',
        'file_link',
    ];

    protected $casts = [
        'intStudentID' => 'integer',
        'tb_mas_requirements_id' => 'integer',
        'submitted_status' => 'boolean',
        'file_link' => 'string',
    ];
}
