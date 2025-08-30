<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $name
 * @property string|null $image_url
 * @property string $type
 * @property float $charge
 * @property bool|int $is_active
 * @property string $pchannel
 * @property string $pmethod
 * @property bool|int $is_nonbank
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class PaymentModeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => (int) $this->id,
            'name'       => (string) $this->name,
            'image_url'  => $this->image_url ? (string) $this->image_url : null,
            'type'       => (string) $this->type,
            'charge'     => isset($this->charge) ? (float) $this->charge : 0.0,
            'is_active'  => (bool) $this->is_active,
            'pchannel'   => (string) $this->pchannel,
            'pmethod'    => (string) $this->pmethod,
            'is_nonbank' => (bool) $this->is_nonbank,
            'created_at' => $this->created_at ? (string) $this->created_at : null,
            'updated_at' => $this->updated_at ? (string) $this->updated_at : null,
        ];
    }
}
