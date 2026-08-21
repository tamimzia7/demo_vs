<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visitor_vin' => $this->visitor_vin,
            'category' => $this->category,
            'amount' => $this->amount,
            'expense_date' => $this->expense_date->toDateString(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
