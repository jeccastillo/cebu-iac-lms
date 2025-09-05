<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Mail;
use App\User;
use App\Models\Course;
use App\Mail\Admissions\ApplicationSubmittedMail;
use App\Mail\Admissions\SendWelcomeMail;

class InquiryAppLead extends Model
{
    //
    use SoftDeletes;

    public function scopeFilterByField($query, $field, $searchData)
    {

        if ($field && $searchData) {
            if ($field == 'name') {
                return $query->where('first_name', 'like', '%' . $searchData . '%')
                              ->orWhere('last_name', 'like', '%' . $searchData . '%');
            } elseif ($field == 'officer') {
                return $query->where('officer', 'like', '%' . $searchData . '%');
            } else {
                return $query->where($field, 'like', '%' . $searchData . '%');
            }
        }
    }

    public function scopeFilterByType($query, $type)
    {
        if ($type) {
            return $query->where('type', $type);
        }
    }

    public function scopeFilterByDepartment($query, $department)
    {
        if ($department) {
            return $query->where('department', $department);
        }
    }

    public function scopeFilterByYear($query, $year)
    {
        if ($year) {
            return $query->where('year', $year);
        }
    }

    public function scopeFilterBySchoolYear($query, $year)
    {
        if ($year) {
            return $query->where('school_year_app', $year);
        }
    }

    public function scopeFilterBySchool($query, $school)
    {
        if ($school) {
            return $query->where('school', 'like', '%' . $school . '%');
        }
    }

    public function scopeFilterByDate($query, $date)
    {
        if ($date) {
            return $query->whereBetween('date', $date);
        }
    }

    public function scopeOrderByField($query, $field, $orderBy)
    {
        if ($field && $orderBy) {
            //if field is status
            return $query->orderBy($field, $orderBy);
        } else {
            return $query->orderBy('created_at', 'DESC');
        }
    }

    public function courses()
    {
        return $this->hasMany(InquireStudCourse::class, 'applicant_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    public function logs()
    {
        return $this->hasMany(InquireStudentLog::class, 'inquire_student_id', 'id')
                    ->orderBy('created_at', 'DESC');
    }

    public function otherSchools()
    {
        return $this->hasMany(InquiryLeadOtherSchool::class, 'lead_id', 'id');
    }

    public function otherPrograms()
    {
        return $this->hasMany(InquiryLeadOtherCourse::class, 'lead_id', 'id');
    }

    public function guidanceOfficer()
    {
        return $this->belongsTo(User::class, 'guidance_user_id', 'id');
    }

    public function sendEmail($leadData)
    {
        //Send email to the selected user / it's need to pre - assigned the email address first in db - HR
        if ($leadData) {
            $toEmail = str_replace(' ', '', $leadData->email);

            $dateExam = @$leadData->date_exam ? date('l, F d, Y', strtotime($leadData->date_exam)) : '';

            Mail::to($toEmail)->send(
                new ApplicationSubmittedMail($leadData, $dateExam)
            );

            $additionalEmails = [
                'mis@iacademy.edu.ph',
                'inquire@iacademy.edu.ph',
                'admissions@iacademy.edu.ph'
            ];

            foreach ($additionalEmails as $key => $toEmail) {
                Mail::to($toEmail)->send(
                    new ApplicationSubmittedMail($leadData, $dateExam)
                );
            }
        }
    }

    public function sendEmailWelcomeInquiry($email)
    {
        //Send email to the selected user / it's need to pre - assigned the email address first in db - HR
        if ($email) {
            $toEmail = str_replace(' ', '', $email);
            Mail::to($toEmail)->send(
                new SendWelcomeMail('', $email)
            );
        }
    }
}
