<?php

namespace Modules\Converter\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:20'],
            'to_base' => ['required', 'numeric', 'gt:0'],
            'is_base' => ['sometimes', 'boolean'],
        ];
    }
}
