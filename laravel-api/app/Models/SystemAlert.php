<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemAlert extends Model
{
    protected $table = 'tb_sys_alerts';

    protected $fillable = [
        'title',
        'message',
        'link',
        'type',
        'target_all',
        'role_codes',
        'campus_ids',
        'starts_at',
        'ends_at',
        'intActive',
        'system_generated',
        'created_by',
    ];

    protected $casts = [
        'target_all'       => 'boolean',
        'role_codes'       => 'array',
        'campus_ids'       => 'array',
        'starts_at'        => 'datetime',
        'ends_at'          => 'datetime',
        'intActive'        => 'integer',
        'system_generated' => 'boolean',
    ];

    public function reads()
    {
        return $this->hasMany(SystemAlertRead::class, 'alert_id');
    }

    /**
     * Convenience: is alert within its active window (or unscheduled).
     */
    public function isWithinWindow(\DateTimeInterface $now = null): bool
    {
        $now = $now ? \Illuminate\Support\Carbon::parse($now) : now();
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->ends_at && $now->gt($this->ends_at)) return false;
        return true;
    }
}
