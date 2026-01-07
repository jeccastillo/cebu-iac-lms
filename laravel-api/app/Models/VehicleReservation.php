<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleReservation extends Model
{
    protected $table = 'tb_mas_vehicle_reservations';
    protected $primaryKey = 'intVehicleReservationID';
    public $timestamps = false;
    protected $fillable = [
        'intVehicleID',
        'intFacultyID',
        'strPurpose',
        'strDestination',
        'dteReservationDate',
        'dteStartTime',
        'dteEndTime',
        'dteReturnDate',
        'intDriverID',
        'strDriverName',
        'strDriverLicense',
        'strContactNumber',
        'intPassengerCount',
        'enumStatus',
        'strRemarks',
        'dteCreated',
        'intApprovedBy',
        'dteApproved',
        'intCreatedBy',
        'dteUpdated'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'intVehicleID', 'intVehicleID');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'intFacultyID', 'intID');
    }

    public function driver()
    {
        return $this->belongsTo(Faculty::class, 'intDriverID', 'intID');
    }

    public function approver()
    {
        return $this->belongsTo(Faculty::class, 'intApprovedBy', 'intID');
    }

    public function creator()
    {
        return $this->belongsTo(Faculty::class, 'intCreatedBy', 'intID');
    }
}
