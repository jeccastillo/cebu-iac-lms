<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDescription extends Model
{
    protected $table = 'payment_descriptions';
    protected $primaryKey = 'intID';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'float',
    ];
}
