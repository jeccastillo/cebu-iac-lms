<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentBilling extends Model
{
    protected $table = 'tb_mas_student_billing';
    protected $primaryKey = 'intID';
    public $timestamps = true;

    protected $fillable = [
        'intStudentID',
        'syid',
        'description',
        'amount',
        'posted_at',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'intStudentID' => 'integer',
        'syid' => 'integer',
        'amount' => 'decimal:2',
        'posted_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];
}
