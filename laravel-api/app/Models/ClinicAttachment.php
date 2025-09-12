<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicAttachment extends Model
{
    // Table name defaults to 'clinic_attachments'

    protected $fillable = [
        'record_id',
        'visit_id',
        'original_name',
        'path',
        'mime',
        'size_bytes',
        'uploaded_by',
    ];
}
