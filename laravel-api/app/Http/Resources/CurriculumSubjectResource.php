<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CurriculumSubjectResource extends JsonResource
{
    /**
     * Maps curriculum-subject association with subject fields.
     * Expected keys (from join):
     * - intID (subject ID), strCode, strDescription, strUnits, intLab
     * - intYearLevel, intSem
     * - curriculum_subject_id
     */
    public function toArray($request): array
    {
        $r = is_array($this->resource) ? $this->resource : (array) $this->resource;

        return [
            'intID'                 => isset($r['intID']) ? (int) $r['intID'] : (isset($r['intID']) ? (int) $r['intID'] : null),
            'strCode'               => $r['strCode'] ?? null,
            'strDescription'        => $r['strDescription'] ?? null,
            'strUnits'              => $r['strUnits'] ?? null,
            'intLab'                => isset($r['intLab']) ? (int) $r['intLab'] : null,
            'intYearLevel'          => isset($r['intYearLevel']) ? (int) $r['intYearLevel'] : null,
            'intSem'                => isset($r['intSem']) ? (int) $r['intSem'] : null,
            'curriculum_subject_id' => isset($r['curriculum_subject_id']) ? (int) $r['curriculum_subject_id'] : null,
        ];
    }
}
