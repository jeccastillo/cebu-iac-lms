<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    protected $table = 'tb_mas_faculty';
    protected $primaryKey = 'intID';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Roles assigned to this faculty.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'tb_mas_faculty_roles', 'intFacultyID', 'intRoleID');
    }

    /**
     * Advisee students currently pointing to this advisor (denormalized via tb_mas_users.intAdvisorID).
     */
    public function advisees(): HasMany
    {
        return $this->hasMany(User::class, 'intAdvisorID', 'intID');
    }

    /**
     * Advisor assignment history rows where this faculty is the advisor.
     */
    public function advisorAssignments(): HasMany
    {
        return $this->hasMany(StudentAdvisor::class, 'intAdvisorID', 'intID');
    }

    /**
     * Check if the faculty has a specific role code.
     */
    public function hasRole(string $code): bool
    {
        return $this->roles()->where('strCode', $code)->exists();
    }

    /**
     * Check if the faculty has any of the given role codes.
     *
     * @param array<string> $codes
     */
    public function hasAnyRole(array $codes): bool
    {
        if (empty($codes)) {
            return false;
        }
        return $this->roles()->whereIn('strCode', $codes)->exists();
    }

    /**
     * Convenience accessor to list role codes.
     *
     * @return array<string>
     */
    public function getRoleCodesAttribute(): array
    {
        return $this->roles()->pluck('strCode')->toArray();
    }
}
