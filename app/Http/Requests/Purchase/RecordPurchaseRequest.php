<?php

namespace App\Http\Requests\Purchase;

use App\Models\Purchase;
use Illuminate\Foundation\Http\FormRequest;

class RecordPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user() === null) {
            return false;
        }

        return $this->user()->can('create', Purchase::class);
    }

    public function rules(): array
    {
        return [
            'offering_id' => ['nullable', 'integer'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'purchased_at' => ['required', 'date', 'before_or_equal:now'],
        ];
    }
}
