<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $table = 'tb_mas_vehicles';
    protected $primaryKey = 'intVehicleID';
    public $timestamps = false;
    protected $fillable = [
        'strPlateNumber',
        'strVehicleName',
        'strBrand',
        'strModel',
        'intYear',
        'enumType',
        'intCapacity',
        'enumTransmission',
        'enumFuelType',
        'strColor',
        'enumStatus',
        'strLocation',
        'decCostPerDay',
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
        return $this->hasMany(ReservationVehicle::class, 'intVehicleID', 'intVehicleID');
    }
}
