<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'tb_mas_invoices';
    protected $primaryKey = 'intID';
    public $timestamps = true;

    protected $fillable = [
        'intStudentID',
        'syid',
        'registration_id',
        'type',
        'status',
        'invoice_number',
        'amount_total',
        'posted_at',
        'due_at',
        'remarks',
        'payload',
        'campus_id',
        'cashier_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'due_at' => 'date',
        'payload' => 'array',
    ];
}
