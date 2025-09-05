<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Course;

class Examinee extends Model
{
    //
    use SoftDeletes;

    public function scopeFilterByField($query, $field, $searchData)
    {
        if ($field && $searchData) {
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

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }
}
