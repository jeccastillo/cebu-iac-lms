<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcessPaymentApplication extends Model
{
    protected $table = 'excess_payment_applications';

    protected $fillable = [
        'student_id',
        'source_term_id',
        'target_term_id',
        'amount',
        'status',
        'created_by',
        'reverted_by',
        'reverted_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'reverted_at' => 'datetime',
    ];
}
