<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class PaymentModeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled via route middleware('role:finance,admin')
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['sometimes', 'string', 'max:64'],
            'image_url'  => ['sometimes', 'nullable', 'url'],
            'type'       => ['sometimes', 'string', 'max:12'],
            'charge'     => ['sometimes', 'numeric', 'min:0'],
            'is_active'  => ['sometimes', 'boolean'],
            'pchannel'   => ['sometimes', 'string', 'max:32'],
            'pmethod'    => ['sometimes', 'string', 'max:32'],
            'is_nonbank' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'       => 'name',
            'image_url'  => 'image URL',
            'type'       => 'type',
            'charge'     => 'charge',
            'is_active'  => 'active flag',
            'pchannel'   => 'payment channel',
            'pmethod'    => 'payment method',
            'is_nonbank' => 'non-bank flag',
        ];
    }
}
