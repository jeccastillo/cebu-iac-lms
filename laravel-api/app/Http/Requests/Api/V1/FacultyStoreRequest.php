<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class FacultyStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by route middleware('role:admin')
        return true;
    }

    public function rules(): array
    {
        return [
            'strUsername'      => ['required', 'string', 'max:50', 'unique:tb_mas_faculty,strUsername'],
            'strPass'          => ['required', 'string', 'min:8'],
            'strFirstname'     => ['required', 'string', 'max:100'],
            'strMiddlename'    => ['sometimes', 'nullable', 'max:100'],
            'strLastname'      => ['required', 'string', 'max:100'],
            'strEmail'         => ['required', 'string', 'email', 'max:150'],
            'strMobileNumber'  => ['required', 'string', 'max:20'],
            'strAddress'       => ['required', 'string', 'max:255'],
            'strDepartment'    => ['required', 'string', 'max:150'],
            'strSchool'        => ['required', 'string', 'max:150'],
            'intUserLevel'     => ['required', 'integer', 'between:0,10'],
            'teaching'         => ['required', 'integer', 'in:0,1'],
            'isActive'         => ['required', 'integer', 'in:0,1'],
            'strFacultyNumber' => ['sometimes', 'nullable', 'string', 'max:50', 'unique:tb_mas_faculty,strFacultyNumber'],
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
