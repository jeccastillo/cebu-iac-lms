<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classlist extends Model
{
    protected $table = 'tb_mas_classlist';
    protected $primaryKey = 'intID';
    public $timestamps = false;

    // Use guarded=[] to allow pragmatic mass assignment; controllers should still whitelist inputs.
    protected $guarded = [];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'intSubjectID', 'intID');
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'intFacultyID', 'intID');
    }

    public function students(): HasMany
    {
        return $this->hasMany(ClasslistStudent::class, 'intClassListID', 'intID');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(RoomSchedule::class, 'intClasslistID', 'intID');
    }
}
