<?php

namespace App\Http\Resources\Clinic;

use Illuminate\Http\Resources\Json\JsonResource;

class VisitResource extends JsonResource
{
    public function toArray($request): array
    {
        $res = is_array($this->resource) ? $this->resource : (array) ($this->resource instanceof \JsonSerializable ? $this->resource->jsonSerialize() : $this->resource);

        return [
            'id' => $res['id'] ?? ($this->id ?? null),
            'record_id' => $res['record_id'] ?? ($this->record_id ?? null),
            'visit_date' => isset($res['visit_date']) ? (string) $res['visit_date'] : (isset($this->visit_date) ? (string) $this->visit_date : null),
            'reason' => $res['reason'] ?? ($this->reason ?? null),
            'triage' => $res['triage'] ?? ($this->triage ?? null),
            'assessment' => $res['assessment'] ?? ($this->assessment ?? null),
            'diagnosis_codes' => $res['diagnosis_codes'] ?? ($this->diagnosis_codes ?? null),
            'treatment' => $res['treatment'] ?? ($this->treatment ?? null),
            'medications_dispensed' => $res['medications_dispensed'] ?? ($this->medications_dispensed ?? null),
            'follow_up' => $res['follow_up'] ?? ($this->follow_up ?? null),
            'campus_id' => $res['campus_id'] ?? ($this->campus_id ?? null),
            'attachments_count' => $res['attachments_count'] ?? ($this->attachments_count ?? 0),
            'created_by' => $res['created_by'] ?? ($this->created_by ?? null),
            'updated_by' => $res['updated_by'] ?? ($this->updated_by ?? null),
            'created_at' => isset($res['created_at']) ? (string) $res['created_at'] : (isset($this->created_at) ? (string) $this->created_at : null),
            'updated_at' => isset($res['updated_at']) ? (string) $res['updated_at'] : (isset($this->updated_at) ? (string) $this->updated_at : null),
        ];
    }
}
