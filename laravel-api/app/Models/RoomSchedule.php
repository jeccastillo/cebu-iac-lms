<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomSchedule extends Model
{
    protected $table = 'tb_mas_room_schedule';
    protected $primaryKey = 'intRoomSchedID';
    public $timestamps = false;
    protected $guarded = [];
}
