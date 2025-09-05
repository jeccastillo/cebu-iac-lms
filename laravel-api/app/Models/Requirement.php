<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    protected $table = 'tb_mas_requirements';
    protected $primaryKey = 'intID';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'type',
        'is_foreign',
        'is_initial_requirements',
    ];

    protected $casts = [
        'is_foreign' => 'boolean',
        'is_initial_requirements' => 'boolean',
    ];
}
