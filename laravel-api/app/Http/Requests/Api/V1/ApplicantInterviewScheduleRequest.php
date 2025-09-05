<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ApplicantInterviewScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware handles role: admissions,admin
        return true;
    }

    // Normalize inputs before validation to a strict SQL datetime string
    protected function prepareForValidation(): void
    {
        $val = $this->input('scheduled_at', null);
        if ($val === null || $val === '') {
            return;
        }

        // If DateTime provided, format directly
        if ($val instanceof \DateTimeInterface) {
            $this->merge([
                'scheduled_at' => $val->format('Y-m-d H:i:s'),
            ]);
            return;
        }

        if (is_string($val)) {
            $s = trim($val);
            // Normalize common variants
            $s = str_replace('T', ' ', $s);
            $s = preg_replace('/Z$/i', '', $s);
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $s)) {
                $s .= ':00';
            }

            // Try strtotime first
            $ts = strtotime($s);
            if ($ts !== false) {
                $this->merge(['scheduled_at' => date('Y-m-d H:i:s', $ts)]);
                return;
            }

            // Fallback: US format M/D/YYYY h:mm AM/PM
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})[ ,T]+(\d{1,2}):(\d{2})\s*(AM|PM)$/i', $s, $m)) {
                $mm = (int) $m[1];
                $dd = (int) $m[2];
                $yyyy = (int) $m[3];
                $hh = (int) $m[4];
                $min = (int) $m[5];
                $mer = strtoupper($m[6]);
                if ($mer === 'PM' && $hh < 12) $hh += 12;
                if ($mer === 'AM' && $hh === 12) $hh = 0;
                $this->merge([
                    'scheduled_at' => sprintf('%04d-%02d-%02d %02d:%02d:00', $yyyy, $mm, $dd, $hh, $min),
                ]);
                return;
            }
        }
        // Leave as-is and let validation fail naturally
    }

    public function rules(): array
    {
        return [
            'applicant_data_id' => ['required', 'integer', 'exists:tb_mas_applicant_data,id'],
            'scheduled_at'      => ['required', 'date_format:Y-m-d H:i:s'],
            'interviewer_user_id' => ['nullable', 'integer'],
            'remarks'           => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'applicant_data_id'   => 'applicant data',
            'scheduled_at'        => 'scheduled date and time',
            'interviewer_user_id' => 'interviewer',
            'remarks'             => 'remarks',
        ];
    }

    public function messages(): array
    {
        return [
            'applicant_data_id.required' => 'Applicant reference is required.',
            'applicant_data_id.exists'   => 'The referenced applicant data was not found.',
            'scheduled_at.required'      => 'Interview schedule is required.',
            'scheduled_at.date_format'   => 'Interview schedule must be a valid date/time.',
            'interviewer_user_id.integer' => 'Interviewer must be a valid user id.',
        ];
    }
}
