<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TuitionYearInstallment extends Model
{
    protected $table = 'tb_mas_tuition_year_installment';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'tuitionyear_id',
        'code',
        'label',
        'dp_type',
        'dp_value',
        'increase_percent',
        'installment_count',
        'is_active',
        'sort_order',
        'level',
    ];

    protected $casts = [
        'dp_value' => 'decimal:2',
        'increase_percent' => 'decimal:2',
        'installment_count' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
