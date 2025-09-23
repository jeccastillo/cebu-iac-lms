<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StudentAdvisorShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is enforced by route middleware('role:faculty_admin,admin')
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id'      => ['sometimes', 'integer', 'min:1'],
            'student_number'  => ['sometimes', 'string', 'max:50'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $id = $this->input('student_id');
            $sn = $this->input('student_number');

            if (empty($id) && empty($sn)) {
                $v->errors()->add('student_id', 'Provide student_id or student_number.');
                $v->errors()->add('student_number', 'Provide student_id or student_number.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'student_id'     => 'student ID',
            'student_number' => 'student number',
        ];
    }
}
