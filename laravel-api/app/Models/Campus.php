<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    protected $table = 'tb_mas_campuses';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'campus_name',
        'description',
        'status',
    ];

    protected $attributes = [
        'status' => 'active',
    ];
}
