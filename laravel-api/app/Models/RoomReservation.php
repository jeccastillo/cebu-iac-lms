<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class RoomReservation extends Model
{
    protected $table = 'tb_mas_room_reservations';
    protected $primaryKey = 'intReservationID';
    public $timestamps = false;
    protected $fillable = [
        'intRoomID',
        'intFacultyID',
        'strPurpose',
        'dteReservationDate',
        'dteStartTime',
        'dteEndTime',
        'enumStatus',
        'strRemarks',
        'dteCreated',
        'intApprovedBy',
        'dteApproved',
        'intCreatedBy',
        'dteUpdated',
        'enumRecurrenceType',
        'intRecurrenceInterval',
        'strRecurrenceDays',
        'dteRecurrenceEnd',
        'intParentReservationID',
        'intMaxCapacity',
        'intSetupTime',
        'intCleanupTime',
        'enumPriority',
        'strContactInfo',
        'boolRequiresApproval'
    ];

    public function room() { return $this->belongsTo(Classroom::class, 'intRoomID', 'intID'); }
    public function faculty() { return $this->belongsTo(Faculty::class, 'intFacultyID', 'intID'); }
    public function approver() { return $this->belongsTo(Faculty::class, 'intApprovedBy', 'intID'); }
    public function creator() { return $this->belongsTo(Faculty::class, 'intCreatedBy', 'intID'); }
    public function parentReservation() { return $this->belongsTo(RoomReservation::class, 'intParentReservationID', 'intReservationID'); }
    public function equipment() { return $this->hasMany(ReservationEquipment::class, 'intReservationID', 'intReservationID'); }
}
