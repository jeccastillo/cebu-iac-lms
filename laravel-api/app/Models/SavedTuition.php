<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedTuition extends Model
{
    protected $table = 'tb_mas_tuition_saved';
    protected $primaryKey = 'intID';
    public $timestamps = true;

    protected $fillable = [
        'intStudentID',
        'intRegistrationID',
        'syid',
        'payload',
        'saved_by',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
