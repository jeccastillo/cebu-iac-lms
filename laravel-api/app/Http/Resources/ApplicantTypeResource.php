<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $intID
 * @property string $name
 * @property string $type
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class ApplicantTypeResource extends JsonResource
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
            'type'       => (string) $this->type,
            'sub_type'   => $this->sub_type !== null ? (string) $this->sub_type : null,
            'created_at' => $this->created_at ? (string) $this->created_at : null,
            'updated_at' => $this->updated_at ? (string) $this->updated_at : null,
        ];
    }
}
