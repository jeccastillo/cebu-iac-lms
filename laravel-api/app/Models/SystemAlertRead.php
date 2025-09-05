<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemAlertRead extends Model
{
    protected $table = 'tb_sys_alert_reads';

    protected $fillable = [
        'alert_id',
        'user_identifier',
        'user_id',
        'login_type',
        'campus_id',
        'dismissed_at',
    ];

    protected $casts = [
        'dismissed_at' => 'datetime',
        'campus_id'    => 'integer',
        'user_id'      => 'integer',
    ];

    public function alert()
    {
        return $this->belongsTo(SystemAlert::class, 'alert_id');
    }
}
