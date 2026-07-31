<?php

namespace Modules\Pos\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchStoreVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prefix' => ['required', 'string', 'max:20'],
            'count' => ['required', 'integer', 'min:1', 'max:100'],
            'discount_type' => ['required', 'string', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'min_purchase' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ];
    }
}
