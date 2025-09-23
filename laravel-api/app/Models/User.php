<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    // Map to existing CI table
    protected $table = 'tb_mas_users';
    protected $primaryKey = 'intID';
    public $timestamps = false;

    // Allow mass assignment for fields we may update via API
    protected $fillable = [
        'strGSuiteEmail',
        'strEmail',
        'strFirstname',
        'strLastname',
        'strStudentNumber',
        'strMobileNumber',
        'intProgramID',
        'intAdvisorID',
    ];

    /**
     * Current advisor (denormalized pointer).
     */
    public function advisor(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'intAdvisorID', 'intID');
    }

    /**
     * Full advisor history (active + ended).
     */
    public function advisorHistory(): HasMany
    {
        return $this->hasMany(StudentAdvisor::class, 'intStudentID', 'intID');
    }
}
