<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // Map to existing CI table
    protected $table = 'tb_mas_users';
    protected $primaryKey = 'intID';
    public $timestamps = false;

    // Allow mass assignment for fields we may update via API
    protected $fillable = [
        'strGSuiteEmail',
        'strEmail',
        'strFirstname',
        'strLastname',
        'strStudentNumber',
        'strMobileNumber',
        'intProgramID',
    ];
}
