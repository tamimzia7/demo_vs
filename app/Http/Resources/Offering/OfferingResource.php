<?php

namespace App\Http\Resources\Offering;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'off' => $this->off,
            'name' => $this->name,
            'metadata' => $this->metadata,
            'active' => $this->active,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
