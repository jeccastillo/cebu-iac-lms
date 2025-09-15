<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentDeficiencyResource extends JsonResource
{
    /**
     * Expects an array/object with keys from DepartmentDeficiencyService::normalizeRow():
     * id, student_id, syid, department_code, payment_description_id, payment_description_name,
     * billing_id, amount, description, remarks, posted_at, campus_id, created_at, updated_at.
     */
    public function toArray($request): array
    {
        // Normalize to array first
        $r = is_array($this->resource) ? $this->resource : (array) $this->resource;

        $id   = isset($r['id']) ? (int) $r['id'] : (isset($r['intID']) ? (int) $r['intID'] : null);
        $sid  = isset($r['student_id']) ? (int) $r['student_id'] : (isset($r['intStudentID']) ? (int) $r['intStudentID'] : null);
        $syid = isset($r['syid']) ? (int) $r['syid'] : null;

        $amount = null;
        if (isset($r['amount'])) {
            $amount = round((float) $r['amount'], 2);
        }

        $desc = null;
        if (isset($r['payment_description_name']) && $r['payment_description_name'] !== null && $r['payment_description_name'] !== '') {
            $desc = (string) $r['payment_description_name'];
        } elseif (isset($r['description'])) {
            $desc = (string) $r['description'];
        }

        return [
            'id'                       => $id,
            'student_id'               => $sid,
            'syid'                     => $syid,
            'department_code'          => isset($r['department_code']) ? (string) $r['department_code'] : null,
            'payment_description_id'   => isset($r['payment_description_id']) ? ($r['payment_description_id'] !== null ? (int) $r['payment_description_id'] : null) : null,
            'payment_description_name' => isset($r['payment_description_name']) ? (string) $r['payment_description_name'] : null,
            'billing_id'               => isset($r['billing_id']) ? ($r['billing_id'] !== null ? (int) $r['billing_id'] : null) : null,
            'amount'                   => $amount,
            'description'              => $desc,
            'remarks'                  => isset($r['remarks']) ? (string) $r['remarks'] : null,
            'posted_at'                => isset($r['posted_at']) ? (string) $r['posted_at'] : null,
            'campus_id'                => isset($r['campus_id']) ? ($r['campus_id'] !== null ? (int) $r['campus_id'] : null) : null,
            'created_at'               => isset($r['created_at']) ? (string) $r['created_at'] : null,
            'updated_at'               => isset($r['updated_at']) ? (string) $r['updated_at'] : null,
        ];
    }
}
