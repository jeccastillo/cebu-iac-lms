<?php

namespace App\Http\Requests\Api\V1\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization enforced at controller via Gate and header-role fallbacks
        return true;
    }

    public function rules(): array
    {
        return [
            // Strictly .xlsx only (same as grades import)
            'file' => ['required', 'file', 'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required'  => 'Please upload an .xlsx file.',
            'file.file'      => 'The upload must be a valid file.',
            'file.mimetypes' => 'Unsupported file type. Please upload an .xlsx file.',
        ];
    }
}
