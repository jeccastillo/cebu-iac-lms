<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentDescription;
use App\Models\StudentBilling;

class StudentDeficiency extends Model
{
    protected $table = 'tb_mas_student_deficiencies';
    protected $primaryKey = 'intID';
    public $timestamps = true;

    protected $fillable = [
        'student_id',
        'syid',
        'department',
        'payment_description_id',
        'billing_id',
        'amount',
        'description',
        'remarks',
        'posted_at',
        'campus_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'student_id'           => 'integer',
        'syid'                   => 'integer',
        'payment_description_id' => 'integer',
        'billing_id'             => 'integer',
        'amount'                 => 'decimal:2',
        'posted_at'              => 'datetime',
        'campus_id'              => 'integer',
        'created_by'             => 'integer',
        'updated_by'             => 'integer',
    ];

    public function paymentDescription()
    {
        return $this->belongsTo(PaymentDescription::class, 'payment_description_id', 'intID');
    }

    public function billing()
    {
        return $this->belongsTo(StudentBilling::class, 'billing_id', 'intID');
    }
}
