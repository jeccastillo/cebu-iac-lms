<?php

namespace App\Http\Resources\Clinic;

use Illuminate\Http\Resources\Json\JsonResource;

class HealthRecordResource extends JsonResource
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

            'created_at' => isset($res['created_at']) ? (string) $res['created_at'] : (isset($this->created_at) ? (string) $this->created_at : null),
            'updated_at' => isset($res['updated_at']) ? (string) $res['updated_at'] : (isset($this->updated_at) ? (string) $this->updated_at : null),
        ];
    }
}
