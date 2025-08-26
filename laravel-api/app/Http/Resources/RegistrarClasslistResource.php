<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RegistrarClasslistResource extends JsonResource
{
    /**
     * Expects an array/object with keys (minimal baseline):
     *  - id, code, description, section, units, schedules, slots, slots_taken_enrolled, finalized
     * Will gracefully map common tb_mas_classlist + subjects fields where present.
     */
    public function toArray($request): array
    {
        $r = is_array($this->resource) ? (object) $this->resource : $this->resource;

        // Common mappings from classlist/subjects joins
        $id           = $r->id           ?? ($r->intID          ?? null);
        $code         = $r->code         ?? ($r->strCode        ?? null);
        $description  = $r->description  ?? ($r->strDescription ?? null);
        $section      = $r->section      ?? ($r->strSection     ?? null);
        $units        = $r->units        ?? ($r->strUnits       ?? null);
        $finalizedVal = $r->finalized    ?? ($r->enumFinalized  ?? null);

        // Normalize finalized: 0=not submitted | 1=submitted | 2=finalized (enum in plan)
        $finalized = null;
        if ($finalizedVal !== null) {
            $finalized = is_numeric($finalizedVal) ? (int) $finalizedVal : (string) $finalizedVal;
        }

        return [
            'id'                    => $id,
            'code'                  => $code,
            'description'           => $description,
            'section'               => $section,
            'units'                 => $units !== null ? (int) $units : null,
            'schedules'             => $r->schedules ?? [], // placeholder until schedule join implemented
            'slots'                 => $r->slots      ?? ($r->intSlot ?? null),
            'slots_taken_enrolled'  => $r->slots_taken_enrolled ?? ($r->intEnrolled ?? null),
            'finalized'             => $finalized,
        ];
    }
}
