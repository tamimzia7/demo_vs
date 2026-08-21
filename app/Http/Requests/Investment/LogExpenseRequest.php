<?php

namespace App\Http\Requests\Investment;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;

class LogExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user() === null) {
            return false;
        }

        return $this->user()->can('create', Expense::class);
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date', 'before_or_equal:now'],
        ];
    }
}
