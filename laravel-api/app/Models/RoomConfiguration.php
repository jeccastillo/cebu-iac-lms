<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomConfiguration extends Model
{
    protected $table = 'tb_mas_room_configurations';
    protected $primaryKey = 'intConfigurationID';
    public $timestamps = false;
    protected $fillable = [
        'intRoomID',
        'strConfigurationName',
        'intMaxCapacity',
        'strDescription',
        'boolIsDefault',
        'enumStatus',
        'dteCreated',
        'dteUpdated'
    ];

    public function room()
    {
        return $this->belongsTo(Classroom::class, 'intRoomID', 'intID');
    }
}
