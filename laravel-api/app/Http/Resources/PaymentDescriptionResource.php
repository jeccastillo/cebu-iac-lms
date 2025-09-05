<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentDescriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'intID'     => $this->intID,
            'name'      => $this->name,
            'amount'    => is_null($this->amount) ? null : (float) $this->amount,
            'campus_id' => isset($this->campus_id) ? (int) $this->campus_id : null,
        ];
    }
}
