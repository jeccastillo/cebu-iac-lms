<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CashierRangeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by route middleware (role:cashier_admin,admin)
        return true;
    }

    public function rules(): array
    {
        return [
            'campus_id'      => ['sometimes', 'nullable', 'integer'],

            'or_start'       => ['sometimes', 'nullable', 'integer', 'min:0'],
            'or_end'         => ['sometimes', 'nullable', 'integer', 'min:0'],

            'invoice_start'  => ['sometimes', 'nullable', 'integer', 'min:0'],
            'invoice_end'    => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $data = $this->all();

            $hasOrStart = array_key_exists('or_start', $data);
            $hasOrEnd   = array_key_exists('or_end', $data);
            if ($hasOrStart xor $hasOrEnd) {
                $v->errors()->add('or', 'Both or_start and or_end must be provided together when updating OR range.');
            }
            if ($hasOrStart && $hasOrEnd && $data['or_start'] !== null && $data['or_end'] !== null) {
                if ((int)$data['or_start'] > (int)$data['or_end']) {
                    $v->errors()->add('or', 'or_start must be less than or equal to or_end.');
                }
            }

            $hasInvStart = array_key_exists('invoice_start', $data);
            $hasInvEnd   = array_key_exists('invoice_end', $data);
            if ($hasInvStart xor $hasInvEnd) {
                $v->errors()->add('invoice', 'Both invoice_start and invoice_end must be provided together when updating Invoice range.');
            }
            if ($hasInvStart && $hasInvEnd && $data['invoice_start'] !== null && $data['invoice_end'] !== null) {
                if ((int)$data['invoice_start'] > (int)$data['invoice_end']) {
                    $v->errors()->add('invoice', 'invoice_start must be less than or equal to invoice_end.');
                }
            }

            if (!$hasOrStart && !$hasInvStart && !array_key_exists('campus_id', $data)) {
                $v->errors()->add('ranges', 'At least one of OR or Invoice ranges or campus_id must be provided.');
            }
        });
    }
}
