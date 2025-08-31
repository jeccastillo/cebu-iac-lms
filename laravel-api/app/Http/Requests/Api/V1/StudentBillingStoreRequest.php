<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StudentBillingStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled by role middleware (finance,admin)
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id'  => ['required', 'integer'],
            'term'        => ['required', 'integer'], // syid
            'description' => ['required', 'string', 'max:255'],
            'amount'      => ['required', 'numeric', 'not_in:0'],
            'posted_at'   => ['sometimes', 'nullable', 'date'],
            'remarks'     => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'student_id'  => 'student id',
            'term'        => 'term (syid)',
            'description' => 'description',
            'amount'      => 'amount',
            'posted_at'   => 'posted at',
            'remarks'     => 'remarks',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        // Normalize keys to internal names
        $data['intStudentID'] = (int) $data['student_id'];
        $data['syid']         = (int) $data['term'];

        // Unset external aliases
        unset($data['student_id'], $data['term']);

        return $data;
    }
}
