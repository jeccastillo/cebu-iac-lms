<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Storage;

class AdmissionFile extends Model
{
    use HasFactory;

    public function getUrlAttribute()
    {
        if ($this->filename) {
            return url('storage/admission_files/' . $this->filename . '.' . $this->filetype);
        }
    }
}
