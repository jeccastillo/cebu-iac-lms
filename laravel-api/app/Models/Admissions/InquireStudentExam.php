<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;

class InquireStudentExam extends Model
{
    //
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }

    public function examinee()
    {
        return $this->belongsTo(Examinee::class, 'examinee_id', 'id');
    }
}
