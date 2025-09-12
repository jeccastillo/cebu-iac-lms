<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranscriptRequest extends Model
{
    protected $table = 'transcript_requests';

    protected $fillable = [
        'student_id',
        'student_number',
        'type',
        'payment_description_id',
        'amount',
        'term_ids',
        'campus_id',
        'date_issued',
        'prepared_by',
        'verified_by',
        'registrar_signatory',
        'signatory',
        'remarks',
        'created_by_faculty_id',
    ];

    protected $casts = [
        'student_id'             => 'integer',
        'student_number'         => 'string',
        'type'                   => 'string',
        'payment_description_id' => 'integer',
        'amount'                 => 'float',
        'term_ids'               => 'array',
        'campus_id'              => 'integer',
        'date_issued'            => 'datetime',
        'prepared_by'            => 'string',
        'verified_by'            => 'string',
        'registrar_signatory'    => 'string',
        'signatory'              => 'string',
        'remarks'                => 'string',
        'created_by_faculty_id'  => 'integer',
    ];
}
