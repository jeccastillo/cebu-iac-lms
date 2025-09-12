<?php

namespace App\Http\Resources\Clinic;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class HealthRecordResource extends JsonResource
{
    public function toArray($request): array
    {
        $res = is_array($this->resource) ? $this->resource : (array) ($this->resource instanceof \JsonSerializable ? $this->resource->jsonSerialize() : $this->resource);

        // Flattened identity fields for SPA rendering
        $personType = $res['person_type'] ?? ($this->person_type ?? null);
        $studentId = $res['person_student_id'] ?? ($this->person_student_id ?? null);
        $facultyId = $res['person_faculty_id'] ?? ($this->person_faculty_id ?? null);

        $studentNumber = null;
        $studentName = null;
        $facultyName = $res['faculty_name'] ?? null;

        // If not provided by service query, derive student identity from tb_mas_users
        if ($personType === 'student' && $studentId) {
            try {
                $stu = DB::table('tb_mas_users')
                    ->select('strStudentNumber', 'strLastname', 'strFirstname', 'strMiddlename')
                    ->where('intID', (int) $studentId)
                    ->first();
                if ($stu) {
                    $studentNumber = $studentNumber ?: ($stu->strStudentNumber ?? null);
                    $ln = $stu->strLastname ?? '';
                    $fn = $stu->strFirstname ?? '';
                    $mn = $stu->strMiddlename ?? '';
                    $name = trim($ln . ($fn ? (', ' . $fn . ($mn ? ' ' . $mn : '')) : ''));
                    $studentName = $studentName ?: ($name !== '' ? $name : null);
                }
            } catch (\Throwable $e) {
                // swallow; keep nulls
            }
        }
        // If not provided by service query, derive faculty identity from tb_mas_faculty
        if ($personType === 'faculty' && $facultyId && $facultyName === null) {
            try {
                $fac = DB::table('tb_mas_faculty')
                    ->select('strLastname', 'strFirstname', 'strMiddlename')
                    ->where('intID', (int) $facultyId)
                    ->first();
                if ($fac) {
                    $ln = $fac->strLastname ?? '';
                    $fn = $fac->strFirstname ?? '';
                    $mn = $fac->strMiddlename ?? '';
                    $name = trim($ln . ($fn ? (', ' . $fn . ($mn ? ' ' . $mn : '')) : ''));
                    $facultyName = $name !== '' ? $name : null;
                }
            } catch (\Throwable $e) {
                // swallow; keep nulls
            }
        }
 
        return [
            'id' => $res['id'] ?? ($this->id ?? null),
            'person_type' => $personType,
            'person_student_id' => $studentId,
            'person_faculty_id' => $facultyId,

            'blood_type' => $res['blood_type'] ?? ($this->blood_type ?? null),
            'height_cm' => isset($res['height_cm']) ? $res['height_cm'] : ($this->height_cm ?? null),
            'weight_kg' => isset($res['weight_kg']) ? $res['weight_kg'] : ($this->weight_kg ?? null),

            'allergies' => $res['allergies'] ?? ($this->allergies ?? null),
            'medications' => $res['medications'] ?? ($this->medications ?? null),
            'immunizations' => $res['immunizations'] ?? ($this->immunizations ?? null),
            'conditions' => $res['conditions'] ?? ($this->conditions ?? null),

            'notes' => $res['notes'] ?? ($this->notes ?? null),
            'campus_id' => $res['campus_id'] ?? ($this->campus_id ?? null),
            'last_updated_by' => $res['last_updated_by'] ?? ($this->last_updated_by ?? null),

            'visits_count' => $res['visits_count'] ?? ($this->visits_count ?? null),

            // Flattened identity for header rendering
            'student_number' => $res['student_number'] ?? $studentNumber,
            'student_name' => $res['student_name'] ?? $studentName,
            'faculty_id' => $res['faculty_id'] ?? $facultyId,
            'faculty_name' => $facultyName,

            'created_at' => isset($res['created_at']) ? (string) $res['created_at'] : (isset($this->created_at) ? (string) $this->created_at : null),
            'updated_at' => isset($res['updated_at']) ? (string) $res['updated_at'] : (isset($this->updated_at) ? (string) $this->updated_at : null),
        ];
    }
}
