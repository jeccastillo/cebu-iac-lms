<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mail;
use App\User;
use App\Models\Course;

class InquireStudent extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'first_name' ,'middle_name', 'last_name',
        'birthdate', 'school', 'year',
        'school_address', 'citizenship', 'isat_score_percent',
        'level', 'gender', 'nationality', 'school_city_area',
        'school_year_app', 'found_out_iacademy','address',
        'mobile_number',
        'date_exam',
        'home_number',
        'source',
        'email',
        'guardian',
        'guardian_number',
        'guardian_email' ,
        'present_address',
        'permanent_address',
        'status',
        'enrolled_date',
        'exam_date',
        'initial_interviewer',
        'initial_remarks',
        'final_interviewer',
        'final_remarks',
        'tour_officer',
        'exam_schedule',
        'ff_status',
    ];

    //for withandwherehas
    public function scopeWithAndWhereHas($query, $relation, $constraint)
    {
        return $query->whereHas($relation, $constraint)->with([$relation => $constraint]);
    }

    public function scopeFilterByField($query, $field, $searchData)
    {
        if ($field == 'selected_course_code' && $searchData) {
            return $query->withAndWhereHas('course', function ($query) use ($searchData) {
                            $query->where('code', 'like', '%' . $searchData . '%');
            });
        } elseif ($field == 'name') {
            return $query->where('first_name', 'like', '%' . $searchData . '%')
                         ->orWhere('last_name', 'like', '%' . $searchData . '%');
        } elseif ($field == 'officer') {
                return $query->where('officer', 'like', '%' . $searchData . '%');
        } elseif ($field != 'selected_course_code' && $searchData) {
            return $query->where($field, 'like', '%' . $searchData . '%');
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

    public function scopeFilterByStatus($query, $searchData)
    {
        if ($searchData) {
            return $query->where('status', $searchData);
        }
    }

    public function scopeFilterByCategory($query, $category)
    {
        if ($category) {
            return $query->where('category', $category);
        }
    }

    public function scopeFilterBySy($query, $sy)
    {
        if ($sy) {
            return $query->where('school_year_app', $sy);
        }
    }

    public function scopeFilterByStatusReport($query, $searchData)
    {
        if ($searchData) {
            if ($searchData == 'enrolled') {
                return $query->where('enrolled_date', '!=', null);
            } elseif ($searchData == 'enrolled_f') {
                return $query->where('enrolled_date', '!=', null)->where('type', 'UG - FRESHMAN');
            } elseif ($searchData == 'enrolled_t') {
                return $query->where('enrolled_date', '!=', null)->where('type', 'UG - TRANSFEREE');
            } elseif ($searchData == 'enrolled_2d') {
                return $query->where('enrolled_date', '!=', null)->where('type', 'UG - 2D');
            } elseif ($searchData == 'reserved') {
                return $query->where('reserved_date', '!=', null);
            } elseif ($searchData == 'reserved_f') {
                return $query->where('reserved_date', '!=', null)->where('type', 'UG - FRESHMAN');
            } elseif ($searchData == 'reserved_t') {
                return $query->where('reserved_date', '!=', null)->where('type', 'UG - TRANSFEREE');
            } elseif ($searchData == 'reserved_2d') {
                return $query->where('reserved_date', '!=', null)->where('type', 'UG - 2D');
            } elseif ($searchData == 'interviewed') {
                return $query->where('interviewed_date', '!=', null)->where('category', 'college');
            } elseif ($searchData == 'took_exam') {
                return $query->where('exam_date', '!=', null);
            }
        }
    }

    public function scopeFilterByStatusTRE($query, $field, $year)
    {
        if ($field == 'reserved_date') {
            return $query->whereYear('reserved_date', $year);
        } else {
            return $query->whereYear('exam_date', $year);
        }
    }

    // Put this in any model and use
    // Modelname::findOrCreate($id);
    public static function findOrCreate($id)
    {
        $obj = static::find($id);
        return $obj ?: new static();
    }

    public function courses()
    {
        return $this->hasMany(InquireStudCourse::class, 'applicant_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'selected_course_id', 'id');
    }

    public function logs()
    {
        return $this->hasMany(InquireStudentLog::class, 'inquire_student_id', 'id')->orderBy('created_at', 'DESC');
    }

    public function pre_requirements()
    {
        return $this->hasMany(InquiryStudPreRequirement::class, 'inquiry_student_id', 'id');
    }

    public function reserved_log()
    {
        return $this->belongsTo(InquireStudentLog::class, 'inquire_student_id', 'id')->where('status', 'reserved');
    }

    public function enrolled_log()
    {
        return $this->belongsTo(InquireStudentLog::class, 'inquire_student_id', 'id')->where('status', 'enrolled');
    }

    public function took_exam_log()
    {
        return $this->belongsTo(InquireStudentLog::class, 'inquire_student_id', 'id')->where('status', 'took_exam');
    }

    public function interviewed_log()
    {
        return $this->belongsTo(InquireStudentLog::class, 'inquire_student_id', 'id')->where('status', 'interviewed');
    }

    public function interviewer_init()
    {
        return $this->belongsTo(User::class, 'initial_interviewer', 'id');
    }

    public function final_interviewer_user()
    {
        return $this->belongsTo(User::class, 'final_interviewer', 'id');
    }

    public function guidance_officer()
    {
        return $this->belongsTo(User::class, 'guidance_user_id', 'id');
    }

    public function interviewer_final()
    {
        return $this->belongsTo(User::class, 'reservation_interviewer', 'id');
    }

    public function officer_tour()
    {
        return $this->belongsTo(User::class, 'tour_officer', 'id');
    }
}
