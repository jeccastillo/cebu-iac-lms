<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;
use App\User;

class InquiryLeadGraph extends Model
{
    //
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
