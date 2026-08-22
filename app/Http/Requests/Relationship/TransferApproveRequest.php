<?php

namespace App\Http\Requests\Relationship;

use Illuminate\Foundation\Http\FormRequest;

class TransferApproveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
