<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarSetting extends Model
{
    protected $table = 'tb_mas_calendar_settings';
    protected $primaryKey = 'intSettingID';
    public $timestamps = false;
    protected $fillable = [
        'intFacultyID',
        'strCalendarProvider',
        'strCalendarToken',
        'strCalendarID',
        'boolAutoSync',
        'boolSyncReservations',
        'boolSyncClasses',
        'intSyncInterval',
        'dteLastSync',
        'enumStatus',
        'dteCreated',
        'dteUpdated'
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'intFacultyID', 'intID');
    }
}
