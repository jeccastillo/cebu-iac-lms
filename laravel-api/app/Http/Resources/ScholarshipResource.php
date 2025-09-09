<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScholarshipResource extends JsonResource
{
    /**
     * Transform the scholarship resource into a standardized array.
     * Handles both Eloquent models and plain arrays.
     */
    public function toArray($request): array
    {
        $r = $this->resource;

        $get = function (string $key) use ($r) {
            if (is_array($r)) {
                return $r[$key] ?? null;
            }
            if (is_object($r)) {
                return $r->$key ?? null;
            }
            return null;
        };

        return [
            'id'                => $get('intID') ?? $get('id'),
            'name'              => $get('name') ?? $get('strName'),
            'code'              => $get('code') ?? null,
            'deduction_type'    => $get('deduction_type') ?? null,
            'deduction_from'    => $get('deduction_from') ?? null,
            'status'            => $get('status') ?? null,
            'max_stacks'        => $get('max_stacks') ?? null,
            'compute_full'      => ($get('compute_full') !== null ? (bool) $get('compute_full') : null),
            'tuition_fee_rate'  => $get('tuition_fee_rate') ?? $get('tuition_fee_rate') ?? null,
            'tuition_fee_fixed' => $get('tuition_fee_fixed') ?? $get('tuition_fee_fixed') ?? null,
            'basic_fee_rate'    => $get('basic_fee_rate') ?? $get('basic_fee_rate') ?? null,
            'basic_fee_fixed'   => $get('basic_fee_fixed') ?? $get('basic_fee_fixed') ?? null,
            'misc_fee_rate'     => $get('misc_fee_rate') ?? $get('misc_fee_rate') ?? null,
            'misc_fee_fixed'    => $get('misc_fee_fixed') ?? $get('misc_fee_fixed') ?? null,
            'lab_fee_rate'      => $get('lab_fee_rate') ?? $get('lab_fee_rate') ?? null,
            'lab_fee_fixed'     => $get('lab_fee_fixed') ?? $get('lab_fee_fixed') ?? null,
            'penalty_fee_rate'  => $get('penalty_fee_rate') ?? $get('penalty_fee_rate') ?? null,
            'penalty_fee_fixed' => $get('penalty_fee_fixed') ?? $get('penalty_fee_fixed') ?? null,
            'other_fees_rate'   => $get('other_fees_rate') ?? $get('other_fees_rate') ?? null,
            'other_fees_fixed'  => $get('other_fees_fixed') ?? $get('other_fees_fixed') ?? null,
            'total_assessment_rate' => $get('total_assessment_rate') ?? $get('total_assessment_rate') ?? null,
            'total_assessment_fixed'=> $get('total_assessment_fixed') ?? $get('total_assessment_fixed') ?? null,
            'description'       => $get('description') ?? null,
        ];
    }
}
