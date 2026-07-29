<?php

namespace Modules\Budget\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'month' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
        ];
    }
}
