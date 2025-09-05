<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;
use App\Models\Course;

class InquireStudCourse extends Model
{
    //
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }
}
