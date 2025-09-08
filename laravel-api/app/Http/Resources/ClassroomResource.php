<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $r = is_array($this->resource) ? $this->resource : (array) $this->resource;

        return [
            'intID'        => isset($r['intID']) ? (int) $r['intID'] : null,
            'enumType'      => $r['enumType']      ?? null,
            'strRoomCode' => isset($r['strRoomCode']) ? (string) $r['strRoomCode'] : null,
            'description' => $r['description'] ?? null,
            'campus_id'    => isset($r['campus_id']) ? (int) $r['campus_id'] : null,
            'campus_name'  => $r['campus_name'] ?? null,
        ];
    }
}
