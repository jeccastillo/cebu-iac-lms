<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacultyDepartment extends Model
{
    protected $table = 'tb_mas_faculty_departments';
    protected $primaryKey = 'intID';
    public $timestamps = true;

    protected $fillable = [
        'intFacultyID',
        'department_code',
        'campus_id',
    ];

    protected $casts = [
        'intFacultyID'    => 'integer',
        'campus_id'       => 'integer',
        'department_code' => 'string',
    ];

    /**
     * Scope by faculty id.
     */
    public function scopeByFaculty($query, int $facultyId)
    {
        return $query->where('intFacultyID', $facultyId);
    }

    /**
     * Scope by department code (lowercased comparison).
     */
    public function scopeByDepartment($query, string $dept)
    {
        return $query->whereRaw('LOWER(department_code) = ?', [strtolower(trim($dept))]);
    }

    /**
     * Get allowed department codes for a faculty (optionally campus-scoped).
     *
     * @return array<int, string>
     */
    public static function allowedForFaculty(int $facultyId, ?int $campusId = null): array
    {
        $q = static::query()->where('intFacultyID', $facultyId);
        if ($campusId !== null) {
            $q->where(function ($qb) use ($campusId) {
                $qb->whereNull('campus_id')->orWhere('campus_id', $campusId);
            });
        }
        $rows = $q->pluck('department_code')->toArray();
        // Normalize: trim whitespace and lowercase codes to avoid mismatches like "registrar " vs "registrar"
        return array_values(array_unique(array_map(function ($c) {
            return strtolower(trim((string) $c));
        }, $rows)));
    }
}
