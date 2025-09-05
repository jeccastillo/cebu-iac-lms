<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cashier extends Model
{
    protected $table = 'tb_mas_cashiers';
    protected $primaryKey = 'intID';
    public $timestamps = false;

    protected $fillable = [
        'user_id',        // deprecated in favor of faculty_id (kept for backward-compat)
        'faculty_id',     // tb_mas_faculty.intID
        'campus_id',
        'or_start',
        'or_end',
        'or_current',
        'invoice_start',
        'invoice_end',
        'invoice_current',
        'temporary_admin',
        'or_used',
        'invoice_used',
    ];

    protected $casts = [
        'user_id'         => 'integer',
        'faculty_id'      => 'integer',
        'campus_id'       => 'integer',
        'or_start'        => 'integer',
        'or_end'          => 'integer',
        'or_current'      => 'integer',
        'invoice_start'   => 'integer',
        'invoice_end'     => 'integer',
        'invoice_current' => 'integer',
        'temporary_admin' => 'integer',
        'or_used'         => 'integer',
        'invoice_used'    => 'integer',
    ];

    // Optional: relationship to users if available in the project.
    // Adjust the related model and foreign/local keys as needed.
    public function user()
    {
        // Assuming standard users table with id primary key
        // If your users PK differs, adjust the third argument accordingly.
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    // Preferred linkage for Cashier ownership: Faculty
    public function faculty()
    {
        return $this->belongsTo(\App\Models\Faculty::class, 'faculty_id', 'intID');
    }
}
