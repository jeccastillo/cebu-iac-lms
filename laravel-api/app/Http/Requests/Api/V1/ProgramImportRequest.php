<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ProgramImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Additionally guarded by role middleware (registrar,admin).
        return true;
    }

    /**
     * Normalize flexible inputs before validation.
     */
    protected function prepareForValidation(): void
    {
        // Allow alternate keys but prefer "file"
        $file = $this->file('file')
            ?? $this->file('upload')
            ?? $this->file('programs')
            ?? null;

        if ($file !== null) {
            $this->files->set('file', $file);
        }

        // Normalize dry_run flags (dry_run or dryRun)
        $dryRun = $this->input('dry_run', $this->input('dryRun', null));
        if ($dryRun !== null) {
            $this->merge([
                'dry_run' => filter_var($dryRun, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240', // 10 MB
            ],
            'dry_run' => [
                'sometimes',
                'boolean',
            ],
            // Optional campus override applied to all rows when provided
            'campus_id' => [
                'sometimes',
                'nullable',
                'integer',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'file' => 'import file',
            'dry_run' => 'dry run',
            'campus_id' => 'campus',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to upload.',
            'file.file' => 'The upload must be a valid file.',
            'file.mimes' => 'Only .xlsx, .xls, or .csv files are supported.',
            'file.max' => 'The file is too large. Please upload a file up to 10MB.',
        ];
    }
}
