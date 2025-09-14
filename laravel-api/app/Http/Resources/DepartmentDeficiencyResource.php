<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentDeficiencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string,mixed>
     */
    public function toArray($request)
    {
        return [
            'id'                        => (int) ($this->intID ?? $this->id ?? 0),
            'student_id'               => (int) ($this->intStudentID ?? $this->student_id ?? 0),
            'syid'                     => isset($this->syid) ? (int) $this->syid : null,
            'department_code'          => isset($this->department_code) ? (string) $this->department_code : null,
            'payment_description_id'   => isset($this->payment_description_id) ? (int) $this->payment_description_id : null,
            'billing_id'               => isset($this->billing_id) ? (int) $this->billing_id : null,
            'amount'                   => isset($this->amount) ? round((float) $this->amount, 2) : null,
            'description'              => isset($this->description) ? (string) $this->description : null,
            'remarks'                  => isset($this->remarks) ? (string) $this->remarks : null,
            'posted_at'                => isset($this->posted_at) ? (string) $this->posted_at : null,
            'campus_id'                => isset($this->campus_id) ? (int) $this->campus_id : null,
            'created_by'               => isset($this->created_by) ? (int) $this->created_by : null,
            'updated_by'               => isset($this->updated_by) ? (int) $this->updated_by : null,
            'created_at'               => isset($this->created_at) ? (string) $this->created_at : null,
            'updated_at'               => isset($this->updated_at) ? (string) $this->updated_at : null,
        ];
    }
}
