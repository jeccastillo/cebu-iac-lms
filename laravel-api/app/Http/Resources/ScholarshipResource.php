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
            'id'              => $get('intID') ?? $get('id'),
            'name'            => $get('name') ?? $get('strName'),
            'code'            => $get('code') ?? null,
            'deduction_type'  => $get('deduction_type') ?? null,
            'deduction_from'  => $get('deduction_from') ?? null,
            'status'          => $get('status') ?? null,
            'percent'         => $get('percent') ?? $get('percentage') ?? null,
            'fixed_amount'    => $get('fixed_amount') ?? $get('amount') ?? null,
            'description'     => $get('description') ?? null,
        ];
    }
}
