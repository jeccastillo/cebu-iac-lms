<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;

class InquiryStudPreRequirement extends Model
{
    //
    public function requirement()
    {
        return $this->belongsTo(InquiryPreRequirement::class, 'requirement_id', 'id');
    }
}
