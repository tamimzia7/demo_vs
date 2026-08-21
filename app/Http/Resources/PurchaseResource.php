<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visitor_vin' => $this->visitor_vin,
            'offering_id' => $this->offering_id,
            'offering_name' => $this->whenLoaded('offering', fn () => $this->offering->name),
            'amount' => $this->amount,
            'purchased_at' => $this->purchased_at->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
