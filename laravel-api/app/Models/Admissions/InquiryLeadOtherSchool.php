<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;

class InquiryLeadOtherSchool extends Model
{
    //
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }
}
