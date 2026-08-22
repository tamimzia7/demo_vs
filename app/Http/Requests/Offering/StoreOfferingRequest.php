<?php

namespace App\Http\Requests\Offering;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfferingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization will be handled by middleware/policy
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'metadata' => 'nullable|array',
            'active' => 'boolean',
        ];
    }
}
