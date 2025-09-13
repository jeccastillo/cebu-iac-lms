<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClasslistAttendance extends Model
{
    protected $table = 'tb_mas_classlist_attendance';
    protected $primaryKey = 'intID';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'is_present' => 'boolean',
    ];

    public function date(): BelongsTo
    {
        return $this->belongsTo(ClasslistAttendanceDate::class, 'intAttendanceDateID', 'intID');
    }

    public function classlist(): BelongsTo
    {
        return $this->belongsTo(Classlist::class, 'intClassListID', 'intID');
    }

    public function classlistStudent(): BelongsTo
    {
        return $this->belongsTo(ClasslistStudent::class, 'intCSID', 'intCSID');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'intStudentID', 'intID');
    }
}
