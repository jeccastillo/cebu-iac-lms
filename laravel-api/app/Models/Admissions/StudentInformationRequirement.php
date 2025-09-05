<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentInformationRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_information_id',
        'admission_upload_type_id',
        'admission_file_id'
    ];

    public function file()
    {
        return $this->belongsTo('App\Models\Admissions\AdmissionFile', 'admission_file_id');
    }

    public function uploadType()
    {
        return $this->belongsTo('App\Models\Admissions\AdmissionUploadType', 'admission_upload_type_id');
    }
}
