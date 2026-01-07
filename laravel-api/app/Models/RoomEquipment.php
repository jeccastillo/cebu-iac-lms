<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomEquipment extends Model
{
    protected $table = 'tb_mas_room_equipment';
    protected $primaryKey = 'intEquipmentID';
    public $timestamps = false;
    protected $fillable = [
        'strEquipmentCode',
        'strEquipmentName',
        'strDescription',
        'enumType',
        'intQuantityAvailable',
        'boolRequiresSetup',
        'intSetupTimeMinutes',
        'strLocation',
        'enumStatus',
        'decCostPerHour',
        'strNotes',
        'dteCreated',
        'dteUpdated',
        'intCreatedBy'
    ];

    public function creator()
    {
        return $this->belongsTo(Faculty::class, 'intCreatedBy', 'intID');
    }
    public function reservations()
    {
        return $this->hasMany(ReservationEquipment::class, 'intEquipmentID', 'intEquipmentID');
    }
}
