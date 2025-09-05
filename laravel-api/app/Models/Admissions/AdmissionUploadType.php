<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionUploadType extends Model
{
    use HasFactory;

    protected $fillable = ['key'];
}
