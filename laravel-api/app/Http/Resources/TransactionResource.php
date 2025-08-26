<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Expects an array with keys (flexible, null-safe):
     * id, student_number, type, method, amount, or_no?, posted_at, remarks?
     */
    public function toArray($request): array
    {
        $res = is_array($this->resource) ? $this->resource : (array) $this->resource;

        return [
            'id'             => $res['id']             ?? null,
            'student_number' => $res['student_number'] ?? null,
            'type'           => $res['type']           ?? null,
            'method'         => $res['method']         ?? null,
            'amount'         => isset($res['amount']) ? (float) $res['amount'] : 0.00,
            'or_no'          => $res['or_no']          ?? null,
            'posted_at'      => $res['posted_at']      ?? null,
            'remarks'        => $res['remarks']        ?? null,
        ];
    }
}
