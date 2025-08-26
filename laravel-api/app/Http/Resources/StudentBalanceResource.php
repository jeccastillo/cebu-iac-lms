<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentBalanceResource extends JsonResource
{
    /**
     * Expects an array with keys:
     * student_number, total_due, total_paid, outstanding, last_payment_date?, ledger?[]
     */
    public function toArray($request): array
    {
        $res = is_array($this->resource) ? $this->resource : (array) $this->resource;

        return [
            'student_number'   => $res['student_number']   ?? null,
            'total_due'        => $res['total_due']        ?? 0.00,
            'total_paid'       => $res['total_paid']       ?? 0.00,
            'outstanding'      => $res['outstanding']      ?? 0.00,
            'last_payment_date'=> $res['last_payment_date']?? null,
            'ledger'           => $res['ledger']           ?? [],
        ];
    }
}
