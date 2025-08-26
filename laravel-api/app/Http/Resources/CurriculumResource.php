<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CurriculumResource extends JsonResource
{
    /**
     * Accepts array|object (DB::table row). Keeps CI column names for parity.
     */
    public function toArray($request): array
    {
        $r = is_array($this->resource) ? $this->resource : (array) $this->resource;

        return [
            'intID'        => isset($r['intID']) ? (int) $r['intID'] : null,
            'strName'      => $r['strName']      ?? null,
            'intProgramID' => isset($r['intProgramID']) ? (int) $r['intProgramID'] : null,
            'program_code' => $r['program_code'] ?? null,
            'active'       => isset($r['active']) ? (int) $r['active'] : null,
            'isEnhanced'   => isset($r['isEnhanced']) ? (int) $r['isEnhanced'] : null,
            'campus_id'    => isset($r['campus_id']) ? (int) $r['campus_id'] : null,
        ];
    }
}
