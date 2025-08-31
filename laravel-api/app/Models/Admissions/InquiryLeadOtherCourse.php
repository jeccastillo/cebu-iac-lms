<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;

class InquiryLeadOtherCourse extends Model
{
    //
    public function other_course()
    {
        return $this->belongsTo(OtherCourse::class, 'other_course_id', 'id');
    }
}
