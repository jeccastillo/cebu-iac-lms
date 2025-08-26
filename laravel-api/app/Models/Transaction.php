<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'tb_mas_transactions';
    protected $primaryKey = 'intTransactionID';
    public $timestamps = false;
    protected $guarded = [];
}
