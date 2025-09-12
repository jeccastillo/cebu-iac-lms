<?php

namespace App\Http\Resources\Clinic;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class HealthRecordListResource extends JsonResource
{
    public function toArray($request): array
    {
        $res = is_array($this->resource) ? $this->resource : (array) ($this->resource instanceof \JsonSerializable ? $this->resource->jsonSerialize() : $this->resource);

        // Derive flattened student display fields for UI convenience
        $studentNumber = $res['student_number'] ?? null;
        $ln = $res['last_name'] ?? null;
        $fn = $res['first_name'] ?? null;
        $mn = $res['middle_name'] ?? null;
        $studentName = null;
        if ($ln || $fn || $mn) {
            $studentName = trim(
                ($ln ? $ln : '')
                . ($fn ? (', ' . $fn . ($mn ? ' ' . $mn : '')) : '')
            );
            if ($studentName === '') {
                $studentName = null;
            }
        }

        // Derive faculty display when missing
        $personType = $res['person_type'] ?? ($this->person_type ?? null);
        $facultyId = $res['person_faculty_id'] ?? ($this->person_faculty_id ?? null);
        $facultyName = $res['faculty_name'] ?? null;
        if ($personType === 'faculty' && $facultyId && $facultyName === null) {
            try {
                $fac = DB::table('tb_mas_faculty')
                    ->select('strLastname', 'strFirstname', 'strMiddlename')
                    ->where('intID', (int)$facultyId)
                    ->first();
                if ($fac) {
                    $ln = $fac->strLastname ?? '';
                    $fn = $fac->strFirstname ?? '';
                    $mn = $fac->strMiddlename ?? '';
                    $name = trim($ln . ($fn ? (', ' . $fn . ($mn ? ' ' . $mn : '')) : ''));
                    $facultyName = $name !== '' ? $name : null;
                }
            } catch (\Throwable $e) {
                // swallow
            }
        }

        return [
            'id' => $res['id'] ?? ($this->id ?? null),
            'person_type' => $res['person_type'] ?? ($this->person_type ?? null),
            'person_student_id' => $res['person_student_id'] ?? ($this->person_student_id ?? null),
            'person_faculty_id' => $res['person_faculty_id'] ?? ($this->person_faculty_id ?? null),
            'blood_type' => $res['blood_type'] ?? ($this->blood_type ?? null),
            'campus_id' => $res['campus_id'] ?? ($this->campus_id ?? null),
            'updated_at' => isset($res['updated_at']) ? (string) $res['updated_at'] : (isset($this->updated_at) ? (string) $this->updated_at : null),

            // Flattened identity fields used by SPA list template
            'student_number' => $studentNumber,
            'student_name' => $studentName,
            'faculty_id' => $res['person_faculty_id'] ?? ($this->person_faculty_id ?? null),
            'faculty_name' => $facultyName,

            // Nested student projection (retained for consumers using it)
            'student' => [
                'student_number' => $studentNumber,
                'last_name' => $ln,
                'first_name' => $fn,
                'middle_name' => $mn,
                'program_id' => $res['program_id'] ?? null,
            ],
        ];
    }
}
