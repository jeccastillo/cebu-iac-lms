<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationEquipment extends Model
{
    protected $table = 'tb_mas_reservation_equipment';
    protected $primaryKey = 'intReservationEquipmentID';
    public $timestamps = false;
    protected $fillable = [
        'intReservationID',
        'intEquipmentID',
        'intQuantityRequested',
        'intQuantityApproved',
        'enumStatus',
        'strNotes',
        'dteCreated',
        'dteUpdated'
    ];

    public function reservation()
    {
        return $this->belongsTo(RoomReservation::class, 'intReservationID', 'intReservationID');
    }
    public function equipment()
    {
        return $this->belongsTo(RoomEquipment::class, 'intEquipmentID', 'intEquipmentID');
    }
}
