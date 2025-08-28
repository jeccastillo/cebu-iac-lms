<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TuitionBreakdownResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        $payload = is_array($this->resource) ? $this->resource : [];

        // Ensure success flag for consumers
        if (!array_key_exists('success', $payload)) {
            $payload = ['success' => true] + $payload;
        }

        return $payload;
    }
}
