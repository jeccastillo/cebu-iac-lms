<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcceptanceLetterAttachment extends Model
{
    use HasFactory;

    public function getUrlAttribute()
    {
        if ($this->filename) {
            return url('storage/acceptance_attachments/' . $this->filename . '.' . $this->filetype);
        }
    }
}
