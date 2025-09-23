<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PayeeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => (int) ($this->id ?? 0),
            'id_number'      => isset($this->id_number) ? (string) $this->id_number : null,
            'firstname'      => isset($this->firstname) ? (string) $this->firstname : null,
            'middlename'     => isset($this->middlename) ? (string) $this->middlename : null,
            'lastname'       => isset($this->lastname) ? (string) $this->lastname : null,
            'tin'            => isset($this->tin) ? (string) $this->tin : null,
            'address'        => isset($this->address) ? (string) $this->address : null,
            'contact_number' => isset($this->contact_number) ? (string) $this->contact_number : null,
            'email'          => isset($this->email) ? (string) $this->email : null,
            'full_name'      => method_exists($this->resource, 'getFullNameAttribute')
                ? (string) $this->resource->full_name
                : trim(
                    (string) ($this->lastname ?? '') . ', ' .
                    (string) ($this->firstname ?? '') . ' ' .
                    (string) ($this->middlename ?? '')
                ),
        ];
    }
}
