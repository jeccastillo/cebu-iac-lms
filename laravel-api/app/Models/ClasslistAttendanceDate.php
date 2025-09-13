<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClasslistAttendanceDate extends Model
{
    protected $table = 'tb_mas_classlist_attendance_date';
    protected $primaryKey = 'intID';
    public $timestamps = false;

    protected $guarded = [];

    public function classlist(): BelongsTo
    {
        return $this->belongsTo(Classlist::class, 'intClassListID', 'intID');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ClasslistAttendance::class, 'intAttendanceDateID', 'intID');
    }
}
