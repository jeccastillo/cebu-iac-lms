<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ClassroomImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route is additionally protected by role middleware.
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|max:10240|mimes:xlsx,xls,csv',
            'dry_run' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please upload a file.',
            'file.file'     => 'Invalid upload payload.',
            'file.max'      => 'File too large (max 10 MB).',
            'file.mimes'    => 'Unsupported file type. Please upload .xlsx, .xls, or .csv.',
        ];
    }
}
