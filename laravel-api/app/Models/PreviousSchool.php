<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreviousSchool extends Model
{
    protected $table = 'previous_schools';
    protected $primaryKey = 'intID';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'city',
        'province',
        'country',
    ];
}
