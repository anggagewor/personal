<?php

namespace Modules\Finance\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Finance\Domain\Enums\FinanceType;

class UpdateFinanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', Rule::enum(FinanceType::class)],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'date' => ['nullable', 'date'],
        ];
    }
}
