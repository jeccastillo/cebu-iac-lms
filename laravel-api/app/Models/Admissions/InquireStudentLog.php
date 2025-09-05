<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;
use App\User;

class InquireStudentLog extends Model
{
    //
    public function student()
    {
        return $this->belongsTo(InquireStudent::class, 'inquire_student_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
