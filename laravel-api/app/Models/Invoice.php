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
        // Extra amounts
        'withholding_tax_percentage',
        'invoice_amount',
        'invoice_amount_ves',
        'invoice_amount_vzrs',
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
        'withholding_tax_percentage' => 'integer',
        'invoice_amount' => 'float',
        'invoice_amount_ves' => 'float',
        'invoice_amount_vzrs' => 'float',
    ];
}
