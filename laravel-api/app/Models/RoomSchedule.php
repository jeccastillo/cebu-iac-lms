<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomSchedule extends Model
{
    protected $table = 'tb_mas_room_schedule';
    protected $primaryKey = 'intRoomSchedID';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Get the classroom associated with this schedule.
     */
    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'intRoomID', 'intID');
    }

    /**
     * Get the academic year/school year associated with this schedule.
     */
    public function sy()
    {
        return $this->belongsTo(SchoolYear::class, 'intSem', 'intID');
    }

    /**
     * Get the classlist associated with this schedule.
     */
    public function classlist()
    {
        return $this->belongsTo(Classlist::class, 'intClasslistID', 'intID');
    }
}
