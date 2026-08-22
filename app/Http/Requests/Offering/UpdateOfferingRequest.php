<?php

namespace App\Http\Requests\Offering;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfferingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization will be handled by middleware/policy
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'metadata' => 'sometimes|nullable|array',
            'active' => 'sometimes|boolean',
        ];
    }
}
