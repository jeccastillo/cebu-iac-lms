<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMode extends Model
{
    use SoftDeletes;

    protected $table = 'payment_modes';

    protected $fillable = [
        'name',
        'image_url',
        'type',
        'charge',
        'is_active',
        'pchannel',
        'pmethod',
        'is_nonbank',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_nonbank' => 'boolean',
        'charge' => 'float',
    ];
}
