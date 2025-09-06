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
}
