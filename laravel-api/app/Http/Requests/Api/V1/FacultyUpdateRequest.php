<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FacultyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by route middleware('role:admin')
        return true;
    }

    public function rules(): array
    {
        $id = (int) ($this->route('id') ?? 0);

        return [
            'strUsername'      => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('tb_mas_faculty', 'strUsername')->ignore($id, 'intID'),
            ],
            'strPass'          => ['sometimes', 'nullable', 'string', 'min:8'],
            'strFirstname'     => ['sometimes', 'string', 'max:100'],
            'strMiddlename'    => ['sometimes', 'nullable', 'max:100'],
            'strLastname'      => ['sometimes', 'string', 'max:100'],
            'strEmail'         => ['sometimes', 'string', 'email', 'max:150'],
            'strMobileNumber'  => ['sometimes', 'string', 'max:20'],
            'strAddress'       => ['sometimes', 'string', 'max:255'],
            'strDepartment'    => ['sometimes', 'string', 'max:150'],
            'strSchool'        => ['sometimes', 'string', 'max:150'],
            'intUserLevel'     => ['sometimes', 'integer', 'between:0,10'],
            'teaching'         => ['sometimes', 'integer', 'in:0,1'],
            'isActive'         => ['sometimes', 'integer', 'in:0,1'],
            'strFacultyNumber' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('tb_mas_faculty', 'strFacultyNumber')->ignore($id, 'intID'),
            ],
            'campus_id'        => ['sometimes', 'nullable', 'integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'strUsername'      => 'username',
            'strPass'          => 'password',
            'strFirstname'     => 'first name',
            'strMiddlename'    => 'middle name',
            'strLastname'      => 'last name',
            'strEmail'         => 'email',
            'strMobileNumber'  => 'mobile number',
            'strAddress'       => 'address',
            'strDepartment'    => 'department',
            'strSchool'        => 'school',
            'intUserLevel'     => 'user level',
            'strFacultyNumber' => 'faculty number',
        ];
    }
}
