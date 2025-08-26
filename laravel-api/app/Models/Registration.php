<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $table = 'tb_mas_registration';
    protected $primaryKey = 'intRegistrationID';
    public $timestamps = false;
    protected $guarded = [];
}
