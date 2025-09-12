<?php

namespace App\Http\Resources\Clinic;

use Illuminate\Http\Resources\Json\JsonResource;

class HealthRecordListResource extends JsonResource
{
    public function toArray($request): array
    {
        $res = is_array($this->resource) ? $this->resource : (array) ($this->resource instanceof \JsonSerializable ? $this->resource->jsonSerialize() : $this->resource);

        return [
            'id' => $res['id'] ?? ($this->id ?? null),
            'person_type' => $res['person_type'] ?? ($this->person_type ?? null),
            'person_student_id' => $res['person_student_id'] ?? ($this->person_student_id ?? null),
            'person_faculty_id' => $res['person_faculty_id'] ?? ($this->person_faculty_id ?? null),
            'blood_type' => $res['blood_type'] ?? ($this->blood_type ?? null),
            'campus_id' => $res['campus_id'] ?? ($this->campus_id ?? null),
            'updated_at' => isset($res['updated_at']) ? (string) $res['updated_at'] : (isset($this->updated_at) ? (string) $this->updated_at : null),

            // Student identity projection (when applicable)
            'student' => [
                'student_number' => $res['student_number'] ?? null,
                'last_name' => $res['last_name'] ?? null,
                'first_name' => $res['first_name'] ?? null,
                'middle_name' => $res['middle_name'] ?? null,
                'program_id' => $res['program_id'] ?? null,
            ],
        ];
    }
}
