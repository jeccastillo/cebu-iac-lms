<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TuitionBreakdownResource extends JsonResource
{
    /**
     * Expected shape (placeholder-friendly):
     * {
     *   summary: {
     *     tuition: float,
     *     misc_total: float,
     *     lab_total: float,
     *     discounts_total: float,
     *     scholarships_total: float,
     *     additional_total: float,
     *     total_due: float
     *   },
     *   items: {
     *     tuition: [{ code?, units?, rate?, amount }],
     *     misc: [{ name, amount }],
     *     lab: [{ name, amount }],
     *     discounts: [{ name, percent?, amount }],
     *     scholarships: [{ name, amount }],
     *     additional: [{ name, amount }]
     *   }
     * }
     */
    public function toArray($request): array
    {
        $res = is_array($this->resource) ? $this->resource : (array) $this->resource;

        $summary = (array) ($res['summary'] ?? []);
        $items   = (array) ($res['items'] ?? []);

        return [
            'summary' => [
                'tuition'            => (float) ($summary['tuition']            ?? 0.0),
                'misc_total'         => (float) ($summary['misc_total']         ?? 0.0),
                'lab_total'          => (float) ($summary['lab_total']          ?? 0.0),
                'discounts_total'    => (float) ($summary['discounts_total']    ?? 0.0),
                'scholarships_total' => (float) ($summary['scholarships_total'] ?? 0.0),
                'additional_total'   => (float) ($summary['additional_total']   ?? 0.0),
                'total_due'          => (float) ($summary['total_due']          ?? 0.0),
            ],
            'items' => [
                'tuition'      => array_values((array)($items['tuition']      ?? [])),
                'misc'         => array_values((array)($items['misc']         ?? [])),
                'lab'          => array_values((array)($items['lab']          ?? [])),
                'discounts'    => array_values((array)($items['discounts']    ?? [])),
                'scholarships' => array_values((array)($items['scholarships'] ?? [])),
                'additional'   => array_values((array)($items['additional']   ?? [])),
            ],
        ];
    }
}
