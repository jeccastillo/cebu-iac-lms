<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
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
            return $query->orderBy('subject', 'ASC');
        }
    }

    public function scopeFilterByType($query, $type)
    {
        if ($type) {
            return $query->where('type', $type);
        }
    }

    public function scopeFilterByTypeAndSort($query, $type)
    {

        if ($type) {
            if ($type == 'computing' || $type == 'business') {
                //math, english, abstract reasoning
                return $query->where('type', $type)
                             ->orderByRaw("FIELD(subject , 'math', 'english', 'Abstract Reasoning') ASC")
                             ->orderBy('item_number', 'ASC');
            } elseif ($type == 'design') {
                //english, visuospatial skills, drawing assesment
                return $query->where('type', $type)
                             ->orderByRaw("FIELD(subject , 'english', 'Visuospatial') ASC")
                             ->orderBy('item_number', 'ASC');
            } else {
                return $query->where('type', $type)->orderBy('subject', 'ASC')
                             ->orderBy('item_number', 'ASC');
            }
        } else {
            return $query->orderBy('item_number', 'ASC');
        }
    }
}
