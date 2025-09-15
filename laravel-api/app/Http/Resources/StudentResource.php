<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Expects an array with keys:
     * first_name, last_name, personal_email, student_number,
     * contact_number, course_id, course_name, last_term, last_term_sy
     */
    public function toArray($request): array
    {
        $res = is_array($this->resource) ? $this->resource : (array) $this->resource;

        return [
            'first_name'     => $res['first_name']     ?? null,
            'last_name'      => $res['last_name']      ?? null,
            'personal_email' => $res['personal_email'] ?? null,
            'student_number' => $res['student_number'] ?? null,
            'student_id'     => $res['student_id'] ?? null,
            'contact_number' => $res['contact_number'] ?? null,
            'course_id'      => $res['course_id']      ?? null,
            'course_name'    => $res['course_name']    ?? null,
            'last_term'      => $res['last_term']      ?? null,
            'last_term_sy'   => $res['last_term_sy']   ?? null,
        ];
    }
}
