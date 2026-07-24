<?php

namespace Modules\Finance\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Finance\Domain\Enums\FinanceType;

class StoreFinanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(FinanceType::class)],
            'amount' => ['required', 'numeric', 'min:0'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'date' => ['required', 'date'],
        ];
    }
}
