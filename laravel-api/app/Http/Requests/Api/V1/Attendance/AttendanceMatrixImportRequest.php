<?php

namespace App\Http\Requests\Api\V1\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceMatrixImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is enforced in controller via Gate and header fallbacks
        return true;
    }

    public function rules(): array
    {
        return [
            'file'   => ['required', 'file', 'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'period' => ['required', 'in:midterm,finals'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required'   => 'Please upload an .xlsx file.',
            'file.file'       => 'The upload must be a valid file.',
            'file.mimetypes'  => 'Unsupported file type. Please upload an .xlsx file.',
            'period.required' => 'Please select a period (midterm or finals).',
            'period.in'       => 'Invalid period. Accepted values are midterm or finals.',
        ];
    }
}
