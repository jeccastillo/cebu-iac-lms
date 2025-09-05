<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $intID
 * @property string $name
 * @property string|null $city
 * @property string|null $province
 * @property string|null $country
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class PreviousSchoolResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => (int) ($this->intID ?? $this->id ?? 0),
            'name'       => (string) $this->name,
            'city'       => $this->city !== null ? (string) $this->city : null,
            'province'   => $this->province !== null ? (string) $this->province : null,
            'country'    => $this->country !== null ? (string) $this->country : null,
            'created_at' => $this->created_at ? (string) $this->created_at : null,
            'updated_at' => $this->updated_at ? (string) $this->updated_at : null,
        ];
    }
}
