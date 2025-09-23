<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payee extends Model
{
    protected $table = 'tb_mas_payee';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_number',
        'firstname',
        'lastname',
        'middlename',
        'tin',
        'address',
        'contact_number',
        'email',
    ];

    public function paymentDetails()
    {
        return $this->hasMany(\App\Models\PaymentDetail::class, 'payee_id', 'id');
    }

    public function getFullNameAttribute(): string
    {
        $parts = [];
        $last = trim((string)($this->lastname ?? ''));
        $first = trim((string)($this->firstname ?? ''));
        $mid = trim((string)($this->middlename ?? ''));
        if ($last !== '') $parts[] = $last . ',';
        if ($first !== '') $parts[] = $first;
        if ($mid !== '') $parts[] = $mid;
        return trim(implode(' ', $parts));
    }
}
