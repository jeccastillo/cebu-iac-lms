<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CashierStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is enforced by route middleware (role:cashier_admin,admin)
        return true;
    }

    public function rules(): array
    {
        return [
            'faculty_id'     => ['required', 'integer', 'exists:tb_mas_faculty,intID'],
            'campus_id'      => ['required', 'integer'],

            // OR range (optional, but if one is present both must be valid)
            'or_start'       => ['nullable', 'integer', 'min:0'],
            'or_end'         => ['nullable', 'integer', 'min:0'],

            // Invoice range (optional, but if one is present both must be valid)
            'invoice_start'  => ['nullable', 'integer', 'min:0'],
            'invoice_end'    => ['nullable', 'integer', 'min:0'],

            'temporary_admin'=> ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $data = $this->all();

            // OR pair check
            $orStartPresent = array_key_exists('or_start', $data) && $data['or_start'] !== null && $data['or_start'] !== '';
            $orEndPresent   = array_key_exists('or_end', $data) && $data['or_end'] !== null && $data['or_end'] !== '';
            if ($orStartPresent xor $orEndPresent) {
                $v->errors()->add('or', 'Both or_start and or_end must be provided together.');
            }
            if ($orStartPresent && $orEndPresent) {
                if ((int)$data['or_start'] > (int)$data['or_end']) {
                    $v->errors()->add('or', 'or_start must be less than or equal to or_end.');
                }
            }

            // Invoice pair check
            $invStartPresent = array_key_exists('invoice_start', $data) && $data['invoice_start'] !== null && $data['invoice_start'] !== '';
            $invEndPresent   = array_key_exists('invoice_end', $data) && $data['invoice_end'] !== null && $data['invoice_end'] !== '';
            if ($invStartPresent xor $invEndPresent) {
                $v->errors()->add('invoice', 'Both invoice_start and invoice_end must be provided together.');
            }
            if ($invStartPresent && $invEndPresent) {
                if ((int)$data['invoice_start'] > (int)$data['invoice_end']) {
                    $v->errors()->add('invoice', 'invoice_start must be less than or equal to invoice_end.');
                }
            }
        });
    }
}
